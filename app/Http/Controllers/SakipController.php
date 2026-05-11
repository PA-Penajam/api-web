<?php

namespace App\Http\Controllers;

use App\Models\Sakip;
use App\Models\SakipRevision;
use App\Support\SakipRevisionSequence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SakipController extends Controller
{
    /**
     * Jenis dokumen SAKIP yang valid
     */
    const JENIS_DOKUMEN = [
        'Indikator Kinerja Utama',
        'Rencana Strategis',
        'Program Kerja',
        'Rencana Kinerja Tahunan',
        'Perjanjian Kinerja',
        'Rencana Aksi',
        'Laporan Kinerja Instansi Pemerintah',
    ];

    private array $allowedFields = [
        'tahun',
        'jenis_dokumen',
        'uraian',
        'link_dokumen',
        'tanggal_publish',
    ];

    /**
     * Ambil semua data SAKIP (PUBLIC - Read Only)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Sakip::with(['revisions' => function ($builder) {
            $builder->orderBy('revisi_ke');
        }]);

        if ($request->has('tahun')) {
            $tahun = filter_var($request->tahun, FILTER_VALIDATE_INT);
            if ($tahun && $tahun >= 2000 && $tahun <= 2100) {
                $query->where('tahun', $tahun);
            }
        }

        $placeholders = implode(',', array_fill(0, count(self::JENIS_DOKUMEN), '?'));
        $data = $query->orderBy('tahun', 'desc')
            ->orderByRaw("FIELD(jenis_dokumen, $placeholders)", self::JENIS_DOKUMEN)
            ->get()
            ->map(fn (Sakip $item) => $this->formatSakip($item))
            ->values();

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $data->count(),
        ]);
    }

    /**
     * Ambil data berdasarkan tahun (PUBLIC - Read Only)
     */
    public function byYear(int $tahun): JsonResponse
    {
        if ($tahun < 2000 || $tahun > 2100) {
            return response()->json([
                'success' => false,
                'message' => 'Tahun tidak valid',
            ], 400);
        }

        $placeholders = implode(',', array_fill(0, count(self::JENIS_DOKUMEN), '?'));
        $data = Sakip::with(['revisions' => function ($builder) {
            $builder->orderBy('revisi_ke');
        }])
            ->where('tahun', $tahun)
            ->orderByRaw("FIELD(jenis_dokumen, $placeholders)", self::JENIS_DOKUMEN)
            ->get()
            ->map(fn (Sakip $item) => $this->formatSakip($item))
            ->values();

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $data->count(),
        ]);
    }

    /**
     * Ambil satu data SAKIP
     */
    public function show(int $id): JsonResponse
    {
        if ($id <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'ID tidak valid',
            ], 400);
        }

        $item = Sakip::find($id);
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatSakip($item),
        ]);
    }

    /**
     * Simpan data SAKIP baru (PROTECTED)
     */
    public function store(Request $request): JsonResponse
    {
        $this->validate($request, [
            'tahun' => 'required|integer|min:2000|max:2100',
            'jenis_dokumen' => 'required|string',
            'uraian' => 'nullable|string',
            'link_dokumen' => 'nullable|string|max:500',
            'tanggal_publish' => 'nullable|date',
            'file_dokumen' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:20480',
        ]);

        if (!in_array($request->jenis_dokumen, self::JENIS_DOKUMEN, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Jenis dokumen tidak valid. Pilihan: ' . implode(', ', self::JENIS_DOKUMEN),
            ], 422);
        }

        $exists = Sakip::where('tahun', $request->tahun)
            ->where('jenis_dokumen', $request->jenis_dokumen)
            ->first();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Data untuk tahun dan jenis dokumen tersebut sudah ada',
            ], 422);
        }

        $data = $this->sanitizeInput($request->only($this->allowedFields));

        if ($request->hasFile('file_dokumen')) {
            $link = $this->uploadFile($request->file('file_dokumen'), $request, 'sakip');
            if ($link) {
                $data['link_dokumen'] = $link;
            }
        }

        $item = Sakip::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil disimpan',
            'data' => $this->formatSakip($item),
        ], 201);
    }

    /**
     * Perbarui data SAKIP (PROTECTED)
     */
    public function update(Request $request, int $id): JsonResponse
    {
        if ($id <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'ID tidak valid',
            ], 400);
        }

        $item = Sakip::find($id);
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        $isRevisi = filter_var($request->input('is_revisi', false), FILTER_VALIDATE_BOOLEAN);
        $rules = [
            'tahun' => 'sometimes|required|integer|min:2000|max:2100',
            'jenis_dokumen' => 'sometimes|required|string',
            'uraian' => 'nullable|string',
            'link_dokumen' => 'nullable|string|max:500',
            'tanggal_publish' => $isRevisi ? 'required|date' : 'nullable|date',
            'is_revisi' => 'nullable|boolean',
            'keterangan_revisi' => 'nullable|string',
            'file_dokumen' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:20480',
        ];
        $this->validate($request, $rules, [
            'tanggal_publish.required' => 'Tanggal publish revisi wajib diisi.',
            'tanggal_publish.date' => 'Tanggal publish harus berupa tanggal yang valid.',
        ]);

        if ($request->has('jenis_dokumen') && !in_array($request->jenis_dokumen, self::JENIS_DOKUMEN, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Jenis dokumen tidak valid. Pilihan: ' . implode(', ', self::JENIS_DOKUMEN),
            ], 422);
        }

        if (!$isRevisi && ($request->has('tahun') || $request->has('jenis_dokumen'))) {
            $tahun = $request->tahun ?? $item->tahun;
            $jenis = $request->jenis_dokumen ?? $item->jenis_dokumen;
            $exists = Sakip::where('tahun', $tahun)
                ->where('jenis_dokumen', $jenis)
                ->where('id', '!=', $id)
                ->first();
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data untuk tahun dan jenis dokumen tersebut sudah ada',
                ], 422);
            }
        }

        if ($isRevisi) {
            return $this->storeRevision($request, $item);
        }

        $data = $this->sanitizeInput($request->only($this->allowedFields));

        if ($request->hasFile('file_dokumen')) {
            $link = $this->uploadFile($request->file('file_dokumen'), $request, 'sakip');
            if ($link) {
                $data['link_dokumen'] = $link;
            }
        }

        $item->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diperbarui',
            'data' => $this->formatSakip($item->fresh()),
        ]);
    }

    /**
     * Hapus data SAKIP (PROTECTED)
     */
    public function destroy(int $id): JsonResponse
    {
        if ($id <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'ID tidak valid',
            ], 400);
        }

        $item = Sakip::find($id);
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus',
        ]);
    }

    private function storeRevision(Request $request, Sakip $item): JsonResponse
    {
        if ($this->isEmptyText($item->link_dokumen)) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada dokumen awal. Simpan dokumen awal terlebih dahulu sebelum membuat revisi.',
            ], 422);
        }

        $revisionLink = $request->input('link_dokumen');
        if ($request->hasFile('file_dokumen')) {
            $uploadedLink = $this->uploadFile($request->file('file_dokumen'), $request, 'sakip');
            if ($uploadedLink) {
                $revisionLink = $uploadedLink;
            }
        }

        if ($this->isEmptyText($revisionLink)) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen revisi wajib diisi melalui link atau file.',
            ], 422);
        }

        $revisionData = $this->sanitizeInput([
            'tanggal_publish' => $request->input('tanggal_publish'),
            'keterangan' => $request->input('keterangan_revisi'),
            'link_dokumen' => $revisionLink,
        ]);

        $currentMax = (int) ($item->revisions()->max('revisi_ke') ?? 0);
        $revisionData['revisi_ke'] = SakipRevisionSequence::next($currentMax);
        $revisionData['sakip_id'] = $item->id;

        $revision = SakipRevision::create($revisionData);

        return response()->json([
            'success' => true,
            'message' => 'Revisi berhasil ditambahkan',
            'data' => $this->formatSakip($item->fresh()),
            'revision' => $this->formatRevision($revision),
        ], 201);
    }

    private function formatSakip(Sakip $item): array
    {
        $revisions = $item->relationLoaded('revisions')
            ? $item->getRelation('revisions')
            : $item->revisions()->orderBy('revisi_ke')->get();

        $formattedRevisions = collect($revisions)
            ->map(fn (SakipRevision $revision) => $this->formatRevision($revision))
            ->values();

        $latestRevision = $formattedRevisions->last();

        return [
            'id' => $item->id,
            'tahun' => $item->tahun,
            'jenis_dokumen' => $item->jenis_dokumen,
            'uraian' => $item->uraian,
            'link_dokumen' => $item->link_dokumen,
            'tanggal_publish' => $this->formatDate($item->tanggal_publish),
            'revisions' => $formattedRevisions,
            'latest_revision' => $latestRevision,
            'dokumen_aktif' => $latestRevision['link_dokumen'] ?? $item->link_dokumen,
            'created_at' => $this->formatTimestamp($item->created_at),
            'updated_at' => $this->formatTimestamp($item->updated_at),
        ];
    }

    private function formatRevision(SakipRevision $revision): array
    {
        return [
            'id' => $revision->id,
            'sakip_id' => $revision->sakip_id,
            'revisi_ke' => $revision->revisi_ke,
            'tanggal_publish' => $this->formatDate($revision->tanggal_publish),
            'keterangan' => $revision->keterangan,
            'link_dokumen' => $revision->link_dokumen,
            'created_at' => $this->formatTimestamp($revision->created_at),
            'updated_at' => $this->formatTimestamp($revision->updated_at),
        ];
    }

    private function formatDate($value): ?string
    {
        if (!$value) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return (string) $value;
    }

    private function formatTimestamp($value): ?string
    {
        if (!$value) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        return (string) $value;
    }

    private function isEmptyText($value): bool
    {
        return trim((string) $value) === '';
    }
}

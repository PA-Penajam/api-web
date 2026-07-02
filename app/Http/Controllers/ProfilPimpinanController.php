<?php

namespace App\Http\Controllers;

use App\Models\ProfilPimpinan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProfilPimpinanController extends Controller
{
    private array $allowedFields = [
        'slug',
        'nama',
        'jabatan',
        'golongan_pangkat',
        'tmt_jabatan',
        'foto_url',
        'profil_link',
        'status_aktif',
        'status_label',
        'urutan',
        'published',
    ];

    public function index(Request $request): JsonResponse
    {
        $query = ProfilPimpinan::query();

        if (!$request->hasHeader('X-API-Key')) {
            $query->where('published', true);
        } elseif ($request->has('published') && $request->published !== '') {
            $query->where('published', filter_var($request->published, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('q') && trim((string) $request->q) !== '') {
            $search = trim(strip_tags((string) $request->q));
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('jabatan', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $data = $query
            ->orderBy('urutan', 'asc')
            ->orderBy('nama', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $data->count(),
        ]);
    }

    public function show(string $idOrSlug): JsonResponse
    {
        $query = ProfilPimpinan::query();

        if (!request()->hasHeader('X-API-Key')) {
            $query->where('published', true);
        }

        $item = ctype_digit($idOrSlug)
            ? $query->find((int) $idOrSlug)
            : $query->where('slug', $idOrSlug)->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $item,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->validate($request, [
            'slug' => 'required|string|max:120|unique:profil_pimpinan,slug',
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'golongan_pangkat' => 'nullable|string|max:255',
            'tmt_jabatan' => 'nullable|date',
            'foto_url' => 'nullable|string|max:500',
            'foto_file' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
            'profil_link' => 'nullable|url|max:500',
            'status_aktif' => 'nullable|in:0,1,true,false',
            'status_label' => 'nullable|string|max:255',
            'urutan' => 'nullable|integer|min:0',
            'published' => 'nullable|in:0,1,true,false',
            'riwayat_pendidikan' => 'nullable|array',
            'riwayat_pendidikan.*.jenjang' => 'required_with:riwayat_pendidikan|string|max:100',
            'riwayat_pendidikan.*.institusi' => 'required_with:riwayat_pendidikan|string|max:255',
            'riwayat_pendidikan.*.tahun' => 'required_with:riwayat_pendidikan|string|max:20',
            'riwayat_pekerjaan' => 'nullable|array',
            'riwayat_pekerjaan.*.jabatan' => 'required_with:riwayat_pekerjaan|string|max:255',
            'riwayat_pekerjaan.*.instansi' => 'required_with:riwayat_pekerjaan|string|max:255',
            'riwayat_pekerjaan.*.tahun' => 'required_with:riwayat_pekerjaan|string|max:20',
            'penghargaan' => 'nullable|array',
            'penghargaan.*.nama' => 'required_with:penghargaan|string|max:255',
            'penghargaan.*.tahun' => 'required_with:penghargaan|string|max:20',
        ]);

        $data = $this->sanitizeInput($request->only($this->allowedFields), ['foto_url', 'profil_link']);
        $data['slug'] = Str::slug((string) $request->input('slug'));
        $data['riwayat_pendidikan'] = $this->normalizeRows($request->input('riwayat_pendidikan', []), ['jenjang', 'institusi', 'tahun']);
        $data['riwayat_pekerjaan'] = $this->normalizeRows($request->input('riwayat_pekerjaan', []), ['jabatan', 'instansi', 'tahun']);
        $data['penghargaan'] = $this->normalizeRows($request->input('penghargaan', []), ['nama', 'tahun']);
        $data['status_aktif'] = filter_var($request->input('status_aktif', true), FILTER_VALIDATE_BOOLEAN);
        $data['published'] = filter_var($request->input('published', false), FILTER_VALIDATE_BOOLEAN);

        if ($request->hasFile('foto_file')) {
            $link = $this->uploadFile($request->file('foto_file'), $request, 'profil-pimpinan');
            if ($link) {
                $data['foto_url'] = $link;
            }
        }

        $item = ProfilPimpinan::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Profil pimpinan berhasil disimpan',
            'data' => $item,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $item = ProfilPimpinan::find($id);
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        $this->validate($request, [
            'slug' => 'sometimes|required|string|max:120|unique:profil_pimpinan,slug,' . $id,
            'nama' => 'sometimes|required|string|max:255',
            'jabatan' => 'sometimes|required|string|max:255',
            'golongan_pangkat' => 'nullable|string|max:255',
            'tmt_jabatan' => 'nullable|date',
            'foto_url' => 'nullable|string|max:500',
            'foto_file' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
            'profil_link' => 'nullable|url|max:500',
            'status_aktif' => 'nullable|in:0,1,true,false',
            'status_label' => 'nullable|string|max:255',
            'urutan' => 'nullable|integer|min:0',
            'published' => 'nullable|in:0,1,true,false',
            'riwayat_pendidikan' => 'nullable|array',
            'riwayat_pendidikan.*.jenjang' => 'required_with:riwayat_pendidikan|string|max:100',
            'riwayat_pendidikan.*.institusi' => 'required_with:riwayat_pendidikan|string|max:255',
            'riwayat_pendidikan.*.tahun' => 'required_with:riwayat_pendidikan|string|max:20',
            'riwayat_pekerjaan' => 'nullable|array',
            'riwayat_pekerjaan.*.jabatan' => 'required_with:riwayat_pekerjaan|string|max:255',
            'riwayat_pekerjaan.*.instansi' => 'required_with:riwayat_pekerjaan|string|max:255',
            'riwayat_pekerjaan.*.tahun' => 'required_with:riwayat_pekerjaan|string|max:20',
            'penghargaan' => 'nullable|array',
            'penghargaan.*.nama' => 'required_with:penghargaan|string|max:255',
            'penghargaan.*.tahun' => 'required_with:penghargaan|string|max:20',
        ]);

        $data = $this->sanitizeInput($request->only($this->allowedFields), ['foto_url', 'profil_link']);

        if ($request->has('slug')) {
            $data['slug'] = Str::slug((string) $request->input('slug'));
        }
        if ($request->has('riwayat_pendidikan')) {
            $data['riwayat_pendidikan'] = $this->normalizeRows($request->input('riwayat_pendidikan', []), ['jenjang', 'institusi', 'tahun']);
        }
        if ($request->has('riwayat_pekerjaan')) {
            $data['riwayat_pekerjaan'] = $this->normalizeRows($request->input('riwayat_pekerjaan', []), ['jabatan', 'instansi', 'tahun']);
        }
        if ($request->has('penghargaan')) {
            $data['penghargaan'] = $this->normalizeRows($request->input('penghargaan', []), ['nama', 'tahun']);
        }
        if ($request->has('status_aktif')) {
            $data['status_aktif'] = filter_var($request->input('status_aktif'), FILTER_VALIDATE_BOOLEAN);
        }
        if ($request->has('published')) {
            $data['published'] = filter_var($request->input('published'), FILTER_VALIDATE_BOOLEAN);
        }

        if ($request->hasFile('foto_file')) {
            $link = $this->uploadFile($request->file('foto_file'), $request, 'profil-pimpinan');
            if ($link) {
                $data['foto_url'] = $link;
            }
        }

        $item->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profil pimpinan berhasil diperbarui',
            'data' => $item->fresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $item = ProfilPimpinan::find($id);
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Profil pimpinan berhasil dihapus',
        ]);
    }

    private function normalizeRows($rows, array $keys): array
    {
        if (!is_array($rows)) {
            return [];
        }

        $normalized = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $item = [];
            foreach ($keys as $key) {
                $value = $row[$key] ?? null;
                if (is_string($value)) {
                    $value = trim(strip_tags($value));
                }
                $item[$key] = $value === '' ? null : $value;
            }

            $hasAnyValue = false;
            foreach ($item as $value) {
                if ($value !== null) {
                    $hasAnyValue = true;
                    break;
                }
            }
            if ($hasAnyValue) {
                $normalized[] = $item;
            }
        }

        return $normalized;
    }
}

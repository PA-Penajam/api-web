<?php

namespace App\Http\Controllers;

use App\Models\Panggilan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PanggilanController extends Controller
{
    /**
     * SECURITY: Daftar kolom yang diizinkan untuk mass assignment
     */
    private array $allowedFields = [
        'tahun_perkara',
        'nomor_perkara',
        'nama_dipanggil',
        'alamat_asal',
        'panggilan_1',
        'panggilan_2',
        'panggilan_ikrar',
        'tanggal_sidang',
        'pip',
        'link_surat',
        'link_pbt',
        'keterangan'
    ];

    /**
     * Ambil semua data panggilan (PUBLIC - Read Only)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Panggilan::query();

        // SECURITY: Validasi dan sanitasi parameter tahun
        if ($request->has('tahun')) {
            $tahun = filter_var($request->tahun, FILTER_VALIDATE_INT);
            if ($tahun && $tahun >= 2000 && $tahun <= 2100) {
                $query->where('tahun_perkara', $tahun);
            }
        }

        // SECURITY: Limit hasil untuk mencegah memory exhaustion.
        // Default 500 dan Max 2000 (konsisten dengan PanggilanEcourtController)
        // agar listing client-side (DataTables di Joomla) menerima seluruh data
        // dalam satu response, bukan terpotong di 10 record.
        $limit = min((int) $request->get('limit', 500), 2000);

        $data = $query->orderBy('created_at', 'desc')
            ->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => $data->items(),
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
            'per_page' => $data->perPage(),
            'total' => $data->total(),
        ]);
    }

    /**
     * Ambil data berdasarkan tahun (PUBLIC - Read Only)
     */
    public function byYear(int $tahun): JsonResponse
    {
        // SECURITY: Validasi tahun
        if ($tahun < 2000 || $tahun > 2100) {
            return response()->json([
                'success' => false,
                'message' => 'Tahun tidak valid'
            ], 400);
        }

        $data = Panggilan::where('tahun_perkara', $tahun)
            ->orderBy('created_at', 'desc')
            ->limit(500)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $data->count()
        ]);
    }

    /**
     * Ambil detail satu data (PUBLIC - Read Only)
     */
    public function show(int $id): JsonResponse
    {
        // SECURITY: Validasi ID positif
        if ($id <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'ID tidak valid'
            ], 400);
        }

        $panggilan = Panggilan::find($id);

        if (!$panggilan) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $panggilan
        ]);
    }

    /**
     * Simpan data baru (PROTECTED - Butuh API Key)
     */
    public function store(Request $request): JsonResponse
    {
        // SECURITY: Validasi ketat untuk semua input
        $this->validate($request, [
            'tahun_perkara' => 'required|integer|min:2000|max:2100',
            'nomor_perkara' => 'required|string|max:50|regex:/^[0-9\/\.a-zA-Z]+$/',
            'nama_dipanggil' => 'required|string|max:255',
            'alamat_asal' => 'nullable|string|max:1000',
            'panggilan_1' => 'nullable|date',
            'panggilan_2' => 'nullable|date',
            'panggilan_ikrar' => 'nullable|date',
            'tanggal_sidang' => 'nullable|date',
            'pip' => 'nullable|string|max:100',
            'file_upload' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120', // Max 5MB
            'file_upload_pbt' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120', // Max 5MB
            'keterangan' => 'nullable|string|max:1000',
        ]);

        // SECURITY: Hanya ambil field yang diizinkan (prevent mass assignment)
        $data = $request->only($this->allowedFields);

        // SECURITY: Sanitasi input teks (skip strip_tags untuk link_surat dan nomor_perkara)
        $data = $this->sanitizeInput($data, ['link_surat', 'link_pbt', 'nomor_perkara']);

        foreach ([
            ['field' => 'file_upload', 'target' => 'link_surat', 'folder' => 'panggilan'],
            ['field' => 'file_upload_pbt', 'target' => 'link_pbt', 'folder' => 'panggilan/pbt'],
        ] as $uploadConfig) {
            if (!$request->hasFile($uploadConfig['field'])) {
                continue;
            }

            $link = $this->uploadFile(
                $request->file($uploadConfig['field']),
                $request,
                $uploadConfig['folder']
            );

            if (!$link) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal upload file ' . $uploadConfig['field'],
                ], 500);
            }

            $data[$uploadConfig['target']] = $link;
        }

        $panggilan = Panggilan::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil disimpan',
            'data' => $panggilan
        ], 201);
    }

    /**
     * Update data (PROTECTED - Butuh API Key)
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // SECURITY: Validasi ID
        if ($id <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'ID tidak valid'
            ], 400);
        }

        $panggilan = Panggilan::find($id);

        if (!$panggilan) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        // SECURITY: Validasi input
        $this->validate($request, [
            'tahun_perkara' => 'sometimes|integer|min:2000|max:2100',
            'nomor_perkara' => 'sometimes|string|max:50|regex:/^[0-9\/\.a-zA-Z]+$/',
            'nama_dipanggil' => 'sometimes|string|max:255',
            'alamat_asal' => 'nullable|string|max:1000',
            'panggilan_1' => 'nullable|date',
            'panggilan_2' => 'nullable|date',
            'panggilan_ikrar' => 'nullable|date',
            'tanggal_sidang' => 'nullable|date',
            'pip' => 'nullable|string|max:100',
            'file_upload' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120', // Max 5MB
            'file_upload_pbt' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120', // Max 5MB
            'keterangan' => 'nullable|string|max:1000',
        ]);

        // SECURITY: Hanya ambil field yang diizinkan
        $data = $request->only($this->allowedFields);

        // SECURITY: Sanitasi input (skip strip_tags untuk link_surat dan nomor_perkara)
        $data = $this->sanitizeInput($data, ['link_surat', 'link_pbt', 'nomor_perkara']);

        $oldLinks = [];

        foreach ([
            ['field' => 'file_upload', 'target' => 'link_surat', 'folder' => 'panggilan'],
            ['field' => 'file_upload_pbt', 'target' => 'link_pbt', 'folder' => 'panggilan/pbt'],
        ] as $uploadConfig) {
            if (!$request->hasFile($uploadConfig['field'])) {
                continue;
            }

            // Simpan link lama untuk cleanup setelah update berhasil
            if ($panggilan->{$uploadConfig['target']}) {
                $oldLinks[] = $panggilan->{$uploadConfig['target']};
            }

            $link = $this->uploadFile(
                $request->file($uploadConfig['field']),
                $request,
                $uploadConfig['folder']
            );

            if (!$link) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal upload file ' . $uploadConfig['field'],
                ], 500);
            }

            $data[$uploadConfig['target']] = $link;
        }

        $panggilan->update($data);

        // Cleanup file lokal lama yang sudah diganti
        foreach ($oldLinks as $oldLink) {
            $this->deleteLocalFile($oldLink);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diupdate',
            'data' => $panggilan->fresh()
        ]);
    }

    /**
     * Hapus data (PROTECTED - Butuh API Key)
     */
    public function destroy(int $id): JsonResponse
    {
        // SECURITY: Validasi ID
        if ($id <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'ID tidak valid'
            ], 400);
        }

        $panggilan = Panggilan::find($id);

        if (!$panggilan) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        // Hapus file lokal terkait sebelum menghapus record
        $this->deleteLocalFile($panggilan->link_surat);
        $this->deleteLocalFile($panggilan->link_pbt);

        $panggilan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus'
        ]);
    }

    /**
     * Hapus file lokal berdasarkan URL (hanya file di /uploads/)
     * File Google Drive tidak dihapus otomatis (memerlukan API terpisah)
     */
    private function deleteLocalFile(?string $url): void
    {
        if (!$url || !str_contains($url, '/uploads/')) {
            return;
        }

        try {
            $path = parse_url($url, PHP_URL_PATH);
            if ($path) {
                $fullPath = app()->basePath('public' . $path);
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                    \Illuminate\Support\Facades\Log::info('File lokal berhasil dihapus', ['path' => $fullPath]);
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal menghapus file lokal', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
        }
    }

}

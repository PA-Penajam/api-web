<?php

namespace App\Http\Controllers;

use App\Models\LaporanKeuangan;
use App\Services\GoogleDriveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controller untuk mengelola data Laporan Keuangan.
 * Menyediakan CRUD operations dengan upload ke Google Drive.
 */
class LaporanKeuanganController extends Controller
{
    /**
     * Daftar field yang diizinkan untuk mass assignment.
     *
     * @var array<int, string>
     */
    private array $allowedFields = [
        'tahun',
        'jenis_satker',
        'periode',
        'judul',
    ];

    /**
     * GET /api/laporan-keuangan
     * Mengambil daftar laporan keuangan dengan pagination dan filter.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = LaporanKeuangan::query();

        // Filter berdasarkan tahun
        if ($request->has('tahun')) {
            $tahun = filter_var($request->input('tahun'), FILTER_VALIDATE_INT);
            if ($tahun !== false && $tahun >= 2000 && $tahun <= 2100) {
                $query->where('tahun', $tahun);
            }
        }

        // Filter berdasarkan jenis satker
        if ($request->has('jenis_satker')) {
            $jenisSatker = trim(strip_tags((string) $request->input('jenis_satker')));
            if (in_array($jenisSatker, ['401877', '401983'], true)) {
                $query->where('jenis_satker', $jenisSatker);
            }
        }

        // Filter berdasarkan periode
        if ($request->has('periode')) {
            $periode = trim(strip_tags((string) $request->input('periode')));
            if (in_array($periode, ['semester_1', 'semester_2', 'tahunan'], true)) {
                $query->where('periode', $periode);
            }
        }

        // Pagination
        $perPage = (int) $request->input('limit', $request->input('per_page', 10));
        $perPage = max(1, min($perPage, 100));

        $result = $query
            ->orderBy('tahun', 'desc')
            ->orderBy('jenis_satker', 'asc')
            ->orderBy('periode', 'asc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $result->items(),
            'total' => $result->total(),
            'current_page' => $result->currentPage(),
            'last_page' => $result->lastPage(),
            'per_page' => $result->perPage(),
        ]);
    }

    /**
     * GET /api/laporan-keuangan/{id}
     * Mengambil detail satu laporan keuangan.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        if ($id <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'ID tidak valid',
            ], 400);
        }

        $data = LaporanKeuangan::find($id);
        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data laporan keuangan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * POST /api/laporan-keuangan
     * Menambah data laporan keuangan baru dengan upload file.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $this->validate($request, [
            'tahun' => 'required|integer|min:2000|max:2100',
            'jenis_satker' => 'required|in:401877,401983',
            'periode' => 'required|in:semester_1,semester_2,tahunan',
            'judul' => 'required|string|max:255',
            'file_upload' => 'required|file|mimes:pdf|max:10240',
            'cover_upload' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $data = $request->only($this->allowedFields);
        $data['judul'] = trim(strip_tags((string) ($data['judul'] ?? '')));
        $data['jenis_satker'] = trim(strip_tags((string) ($data['jenis_satker'] ?? '')));
        $data['periode'] = trim(strip_tags((string) ($data['periode'] ?? '')));

        try {
            $data['file_url'] = $this->uploadToGoogleDrive($request->file('file_upload'), $request, 'uploads/laporan-keuangan');

            if ($request->hasFile('cover_upload')) {
                $data['cover_url'] = $this->uploadToGoogleDrive($request->file('cover_upload'), $request, 'uploads/laporan-keuangan/covers');
            }

            $created = LaporanKeuangan::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Data laporan keuangan berhasil ditambahkan',
                'data' => $created,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data laporan keuangan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * PUT /api/laporan-keuangan/{id}
     * Mengupdate data laporan keuangan.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        if ($id <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'ID tidak valid',
            ], 400);
        }

        $report = LaporanKeuangan::find($id);
        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Data laporan keuangan tidak ditemukan',
            ], 404);
        }

        $this->validate($request, [
            'tahun' => 'required|integer|min:2000|max:2100',
            'jenis_satker' => 'required|in:401877,401983',
            'periode' => 'required|in:semester_1,semester_2,tahunan',
            'judul' => 'required|string|max:255',
            'file_upload' => 'nullable|file|mimes:pdf|max:10240',
            'cover_upload' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $data = $request->only($this->allowedFields);
        $data['judul'] = trim(strip_tags((string) ($data['judul'] ?? '')));
        $data['jenis_satker'] = trim(strip_tags((string) ($data['jenis_satker'] ?? '')));
        $data['periode'] = trim(strip_tags((string) ($data['periode'] ?? '')));

        try {
            if ($request->hasFile('file_upload')) {
                $data['file_url'] = $this->uploadToGoogleDrive($request->file('file_upload'), $request, 'uploads/laporan-keuangan');
            }

            if ($request->hasFile('cover_upload')) {
                $data['cover_url'] = $this->uploadToGoogleDrive($request->file('cover_upload'), $request, 'uploads/laporan-keuangan/covers');
            }

            $report->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Data laporan keuangan berhasil diupdate',
                'data' => $report->fresh(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate data laporan keuangan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /api/laporan-keuangan/{id}
     * Menghapus data laporan keuangan.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        if ($id <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'ID tidak valid',
            ], 400);
        }

        $data = LaporanKeuangan::find($id);
        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data laporan keuangan tidak ditemukan',
            ], 404);
        }

        try {
            $data->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data laporan keuangan berhasil dihapus',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper: Upload file ke Google Drive dan return URL-nya.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param Request $request
     * @param string $folder
     * @return string
     * @throws \Exception
     */
    private function uploadToGoogleDrive($file, Request $request, string $folder): string
    {
        $driveService = new GoogleDriveService();
        $result = $driveService->uploadFile($file, $folder);

        if (!$result || !isset($result['webViewLink'])) {
            throw new \Exception('Gagal upload file ke Google Drive');
        }

        return $result['webViewLink'];
    }
}

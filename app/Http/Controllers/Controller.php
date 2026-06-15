<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * Sanitasi input string untuk mencegah XSS
     * SECURITY: strip_tags + trim pada semua field string
     * Ref: https://medium.com/@developerawam/laravel-security-checklist
     *
     * @param array $data Data input dari request
     * @param array $skipFields Field yang tidak perlu disanitasi (misal: password, html content)
     * @return array Data yang sudah disanitasi
     */
    protected function sanitizeInput(array $data, array $skipFields = []): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value) && !in_array($key, $skipFields)) {
                $data[$key] = trim(strip_tags($value));
                if ($data[$key] === '') {
                    $data[$key] = null;
                }
            }
        }
        return $data;
    }

    /**
     * Upload file ke Google Drive atau penyimpanan lokal
     * SECURITY: Validasi MIME type dari konten file, bukan hanya ekstensi
     *
     * @param \Illuminate\Http\UploadedFile $file File yang diupload
     * @param \Illuminate\Http\Request $request Request object
     * @param string $folder Subfolder di public/uploads/
     * @return string|null URL file yang diupload, atau null jika gagal
     */
    protected function uploadFile($file, \Illuminate\Http\Request $request, string $folder = 'uploads'): ?string
    {
        // SECURITY: Validasi MIME type berdasarkan konten file (magic bytes),
        // bukan hanya ekstensi yang bisa dimanipulasi
        $allowedMimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg',
            'image/png',
            'image/webp',
        ];
        $realMime = $file->getMimeType();
        if (!in_array($realMime, $allowedMimes)) {
            \Illuminate\Support\Facades\Log::warning('Upload ditolak: MIME type tidak diizinkan', [
                'mime' => $realMime,
                'original_name' => $file->getClientOriginalName(),
            ]);
            return null;
        }

        // Coba upload ke Google Drive terlebih dahulu
        try {
            if (class_exists('\App\Services\GoogleDriveService')) {
                $driveService = new \App\Services\GoogleDriveService();
                return $driveService->upload($file);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Google Drive upload gagal', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }

        // Fallback ke penyimpanan lokal
        try {
            // SECURITY: Gunakan random bytes untuk nama file agar tidak bisa ditebak
            $extension = $file->getClientOriginalExtension();
            $filename = bin2hex(random_bytes(16)) . '.' . $extension;
            $destinationPath = app()->basePath('public/uploads/' . $folder);
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $file->move($destinationPath, $filename);
            return $request->root() . '/uploads/' . $folder . '/' . $filename;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Local upload gagal', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return null;
        }
    }
}

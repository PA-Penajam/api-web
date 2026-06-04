<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaporanKeuanganSeeder extends Seeder
{
    /**
     * Seed data laporan keuangan dari website lama.
     * Data disesuaikan dengan struktur periode baru (4 opsi).
     */
    public function run(): void
    {
        $data = [
            // Tahun 2023 - Tahunan (mapping ke Audited)
            [
                'tahun' => 2023,
                'jenis_satker' => '401877',
                'periode' => 'audited',
                'judul' => 'Laporan Keuangan SATKER 401877 Tahun 2023 (Audited)',
                'file_url' => 'https://drive.google.com/file/d/1OFErS0GQkSyM3zB5w0nwlslktXbxMfPu/view?usp=sharing',
                'cover_url' => 'https://pa-penajam.go.id/images/picture/laporan_keuangan_401877_2023.png',
            ],
            [
                'tahun' => 2023,
                'jenis_satker' => '401983',
                'periode' => 'audited',
                'judul' => 'Laporan Keuangan SATKER 401983 Tahun 2023 (Audited)',
                'file_url' => 'https://drive.google.com/file/d/1T0KIWyGcX4IHGvSBoSeBXmUwUk_EVM2A/view?usp=sharing',
                'cover_url' => 'https://pa-penajam.go.id/images/picture/laporan_keuangan_401983_2023.png',
            ],

            // Tahun 2024 - Semester 1
            [
                'tahun' => 2024,
                'jenis_satker' => '401877',
                'periode' => 'semester_1',
                'judul' => 'Laporan Keuangan SATKER 401877 Semester 1 Tahun 2024',
                'file_url' => 'https://drive.google.com/file/d/1CkGaRh_7PO5_rP39bbqeTIvMYCak6hRa/view?usp=sharing',
                'cover_url' => 'https://pa-penajam.go.id/images/picture/laporan_keuangan_401877_S1_2024.png',
            ],
            [
                'tahun' => 2024,
                'jenis_satker' => '401983',
                'periode' => 'semester_1',
                'judul' => 'Laporan Keuangan SATKER 401983 Semester 1 Tahun 2024',
                'file_url' => 'https://drive.google.com/file/d/1cexSd8MIp3fAYtfsHUw8EfKcMq9YahXY/view?usp=sharing',
                'cover_url' => 'https://pa-penajam.go.id/images/picture/laporan_keuangan_401983_S1_2024.png',
            ],

            // Tahun 2025 - Semester 1
            [
                'tahun' => 2025,
                'jenis_satker' => '401877',
                'periode' => 'semester_1',
                'judul' => 'Laporan Keuangan SATKER 401877 Semester 1 Tahun 2025',
                'file_url' => 'https://drive.google.com/file/d/1lj8roxN1iSx0TwEkd31sYlpNAXz-ogFR/view?usp=sharing',
                'cover_url' => 'https://pa-penajam.go.id/images/Sampul_LAPORAN_Keuangan_ES_I_BUD_2025.png',
            ],
            [
                'tahun' => 2025,
                'jenis_satker' => '401983',
                'periode' => 'semester_1',
                'judul' => 'Laporan Keuangan SATKER 401983 Semester 1 Tahun 2025',
                'file_url' => 'https://drive.google.com/file/d/1EWeSZmzPQf09A_f84DZoYTpsdUMj_odD/view?usp=sharing',
                'cover_url' => 'https://pa-penajam.go.id/images/Sampul_LAPORAN_Keuangan_401983_ES_I_BUD_2025.png',
            ],
            // Note: Triwulan 3 di-skip karena tidak sesuai struktur baru
        ];

        foreach ($data as $item) {
            DB::table('laporan_keuangan')->insert([
                'tahun' => $item['tahun'],
                'jenis_satker' => $item['jenis_satker'],
                'periode' => $item['periode'],
                'judul' => $item['judul'],
                'file_url' => $item['file_url'],
                'cover_url' => $item['cover_url'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        $this->command->info('✅ Seeded 6 laporan keuangan (2023: 2, 2024: 2, 2025: 2)');
        $this->command->info('ℹ️  Triwulan 3 di-skip karena tidak sesuai struktur periode baru');
    }
}

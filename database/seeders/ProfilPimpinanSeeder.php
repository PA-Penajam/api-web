<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ProfilPimpinanSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $record = [
            'slug' => 'ketua',
            'nama' => 'Nahdiyanti, S.H.I., M.H.',
            'jabatan' => 'Ketua Pengadilan Agama Penajam',
            'golongan_pangkat' => 'Hakim Madya Pratama, IV/a',
            'tmt_jabatan' => '2026-05-21',
            // Sumber lama hanya memberi path relatif. Simpan null dulu agar
            // admin bisa mengunggah foto final yang stabil/public.
            'foto_url' => null,
            'profil_link' => 'https://simtepa.mahkamahagung.go.id/share/profil_ketua/html/32aecd3a9ffbed0a7ead3aa15f7a29ff',
            'status_aktif' => true,
            'status_label' => 'Aktif',
            'urutan' => 10,
            'published' => true,
            'riwayat_pendidikan' => json_encode([
                [
                    'jenjang' => 'S2',
                    'institusi' => 'Universitas Muslim Indonesia',
                    'tahun' => '2021',
                ],
                [
                    'jenjang' => 'S1',
                    'institusi' => 'Universitas Islam Negeri Alauddin Makassar',
                    'tahun' => '2006',
                ],
                [
                    'jenjang' => 'SLTA',
                    'institusi' => 'Sekolah Menengah Umum Islam Athirah',
                    'tahun' => '1999',
                ],
                [
                    'jenjang' => 'SLTP',
                    'institusi' => 'Madrasah Tsanawiyah Ponorogo Jawa Timur',
                    'tahun' => '1996',
                ],
                [
                    'jenjang' => 'SD',
                    'institusi' => 'SDN 005 Air Putih Samarinda',
                    'tahun' => '1990',
                ],
            ]),
            'riwayat_pekerjaan' => json_encode([
                [
                    'jabatan' => 'Ketua',
                    'instansi' => 'Pengadilan Agama Penajam',
                    'tahun' => '2026',
                ],
                [
                    'jabatan' => 'Wakil Ketua',
                    'instansi' => 'Pengadilan Agama Penajam',
                    'tahun' => '2022',
                ],
                [
                    'jabatan' => 'Hakim',
                    'instansi' => 'Pengadilan Agama Tenggarong',
                    'tahun' => '2020',
                ],
                [
                    'jabatan' => 'Hakim',
                    'instansi' => 'Pengadilan Agama Barru',
                    'tahun' => '2016',
                ],
                [
                    'jabatan' => 'Hakim',
                    'instansi' => 'Pengadilan Agama Masamba',
                    'tahun' => '2010',
                ],
                [
                    'jabatan' => 'Cakim / PNS',
                    'instansi' => 'Pengadilan Agama Samarinda',
                    'tahun' => '2008',
                ],
                [
                    'jabatan' => 'Cakim / CPNS',
                    'instansi' => 'Pengadilan Agama Samarinda',
                    'tahun' => '2007',
                ],
            ]),
            'penghargaan' => json_encode([
                [
                    'nama' => 'SATYA KARYA DWIWINDU',
                    'tahun' => '2023',
                ],
                [
                    'nama' => 'SATYALANCANA KARYA SATYA X TAHUN',
                    'tahun' => '2019',
                ],
            ]),
            'created_at' => $now,
            'updated_at' => $now,
        ];

        DB::table('profil_pimpinan')->updateOrInsert(
            ['slug' => $record['slug']],
            $record
        );

        $this->command?->info('ProfilPimpinanSeeder: profil ketua diproses.');
    }
}

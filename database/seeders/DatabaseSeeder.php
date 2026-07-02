<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            KelompokJabatanSeeder::class,
            JenisPegawaiSeeder::class,
            UraianTugasSeeder::class,
            PanggilanSeeder::class,
            ItsbatSeeder::class,
            PanggilanEcourtSeeder::class,
            LhkpnSeeder::class,
            AnggaranSeeder::class,
            DipaPokSeeder::class,
            AsetBmnSeeder::class,
            SakipSeeder::class,
            LaporanPengaduanSeeder::class,
            KeuanganPerkaraSeeder::class,
            SisaPanjarSeeder::class,
            MediasiSeeder::class,
            SurveyLaporanSeeder::class,
            SurveyPekanSeeder::class,
            ProfilPimpinanSeeder::class,
        ]);
    }
}

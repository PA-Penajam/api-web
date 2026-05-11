<?php

/** @var \Laravel\Lumen\Routing\Router $router */

// Health check
$router->get('/', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API Panggilan Ghaib PA Penajam'
    ]);
});

// SECURITY: Public routes dengan rate limiting (100 request/menit)
$router->group(['prefix' => 'api', 'middleware' => 'throttle:100,1'], function () use ($router) {
    // Panggilan Ghaib
    $router->get('panggilan', 'PanggilanController@index');
    $router->get('panggilan/{id:[0-9]+}', 'PanggilanController@show');
    $router->get('panggilan/tahun/{tahun:[0-9]+}', 'PanggilanController@byYear');

    // Itsbat Nikah
    $router->get('itsbat', 'ItsbatNikahController@index');
    $router->get('itsbat/{id:[0-9]+}', 'ItsbatNikahController@show');

    // Panggilan e-Court
    $router->get('panggilan-ecourt', 'PanggilanEcourtController@index');
    $router->get('panggilan-ecourt/{id:[0-9]+}', 'PanggilanEcourtController@show');
    $router->get('panggilan-ecourt/tahun/{tahun:[0-9]+}', 'PanggilanEcourtController@byYear');

    // Agenda Pimpinan
    $router->get('agenda', 'AgendaPimpinanController@index');
    $router->get('agenda/{id:[0-9]+}', 'AgendaPimpinanController@show');

    // LHKPN Routes
    $router->get('lhkpn', 'LhkpnController@index');
    $router->get('lhkpn/{id:[0-9]+}', 'LhkpnController@show');

    // Realisasi Anggaran Routes
    $router->get('anggaran', 'RealisasiAnggaranController@index');
    $router->get('anggaran/{id:[0-9]+}', 'RealisasiAnggaranController@show');
    $router->get('pagu', 'PaguAnggaranController@index');

    // DIPA POK Routes
    $router->get('dipapok', 'DipaPokController@index');
    $router->get('dipapok/{id:[0-9]+}', 'DipaPokController@show');

    // Aset BMN Routes
    $router->get('aset-bmn', 'AsetBmnController@index');
    $router->get('aset-bmn/{id:[0-9]+}', 'AsetBmnController@show');

    // SAKIP Routes
    $router->get('sakip', 'SakipController@index');
    $router->get('sakip/{id:[0-9]+}', 'SakipController@show');
    $router->get('sakip/tahun/{tahun:[0-9]+}', 'SakipController@byYear');

    // Laporan Pengaduan Routes
    $router->get('laporan-pengaduan', 'LaporanPengaduanController@index');
    $router->get('laporan-pengaduan/{id:[0-9]+}', 'LaporanPengaduanController@show');
    $router->get('laporan-pengaduan/tahun/{tahun:[0-9]+}', 'LaporanPengaduanController@byYear');

    // Keuangan Perkara Routes
    $router->get('keuangan-perkara', 'KeuanganPerkaraController@index');
    $router->get('keuangan-perkara/{id:[0-9]+}', 'KeuanganPerkaraController@show');
    $router->get('keuangan-perkara/tahun/{tahun:[0-9]+}', 'KeuanganPerkaraController@byYear');

    // Sisa Panjar Routes
    $router->get('sisa-panjar', 'SisaPanjarController@index');
    $router->get('sisa-panjar/{id:[0-9]+}', 'SisaPanjarController@show');
    $router->get('sisa-panjar/tahun/{tahun:[0-9]+}', 'SisaPanjarController@byYear');

    // MOU Routes
    $router->get('mou', 'MouController@index');
    $router->get('mou/{id:[0-9]+}', 'MouController@show');

    $router->get('lra', 'LraReportController@index');
    $router->get('lra/{id:[0-9]+}', 'LraReportController@show');

    // Mediasi Routes
    $router->get('mediasi-sk', 'MediasiSkController@index');
    $router->get('mediasi-sk/{id:[0-9]+}', 'MediasiSkController@show');
    $router->get('mediator-banners', 'MediatorBannerController@index');
    $router->get('mediator-banners/{id:[0-9]+}', 'MediatorBannerController@show');

    // SK Inovasi Routes
    $router->get('sk-inovasi', 'SkInovasiController@index');
    $router->get('sk-inovasi/{id:[0-9]+}', 'SkInovasiController@show');

    // Inovasi Routes
    $router->get('inovasi', 'InovasiController@index');
    $router->get('inovasi/{id:[0-9]+}', 'InovasiController@show');

    // Kelompok Jabatan Routes
    $router->get('kelompok-jabatan', 'KelompokJabatanController@index');
    $router->get('kelompok-jabatan/{id:[0-9]+}', 'KelompokJabatanController@show');

    // Jenis Pegawai Routes
    $router->get('jenis-pegawai', 'JenisPegawaiController@index');
    $router->get('jenis-pegawai/{id:[0-9]+}', 'JenisPegawaiController@show');

    // Uraian Tugas Routes
    $router->get('uraian-tugas', 'UraianTugasController@index');
    $router->get('uraian-tugas/{id:[0-9]+}', 'UraianTugasController@show');

    // Survey Laporan Routes (IKM, IPAK, Tindak Lanjut)
    $router->get('survey-laporan', 'SurveyLaporanController@index');
    $router->get('survey-laporan/{id:[0-9]+}', 'SurveyLaporanController@show');
    $router->get('survey-laporan/tahun/{tahun:[0-9]+}', 'SurveyLaporanController@byYear');

    // Survey Pekan Routes (snapshot mingguan)
    $router->get('survey-pekan', 'SurveyPekanController@index');
    $router->get('survey-pekan/{id:[0-9]+}', 'SurveyPekanController@show');
    $router->get('survey-pekan/tahun/{tahun:[0-9]+}', 'SurveyPekanController@byYear');
});

// SECURITY: Protected routes dengan API Key + rate limiting (100 request/menit)
$router->group(['prefix' => 'api', 'middleware' => ['api.key', 'throttle:100,1']], function () use ($router) {
    // Panggilan Ghaib
    $router->post('panggilan', 'PanggilanController@store');
    $router->put('panggilan/{id:[0-9]+}', 'PanggilanController@update');
    $router->post('panggilan/{id:[0-9]+}', 'PanggilanController@update');
    $router->delete('panggilan/{id:[0-9]+}', 'PanggilanController@destroy');

    // Itsbat Nikah
    $router->post('itsbat', 'ItsbatNikahController@store');
    $router->put('itsbat/{id:[0-9]+}', 'ItsbatNikahController@update');
    $router->post('itsbat/{id:[0-9]+}', 'ItsbatNikahController@update');
    $router->delete('itsbat/{id:[0-9]+}', 'ItsbatNikahController@destroy');

    // Panggilan e-Court
    $router->post('panggilan-ecourt', 'PanggilanEcourtController@store');
    $router->put('panggilan-ecourt/{id:[0-9]+}', 'PanggilanEcourtController@update');
    $router->post('panggilan-ecourt/{id:[0-9]+}', 'PanggilanEcourtController@update');
    $router->delete('panggilan-ecourt/{id:[0-9]+}', 'PanggilanEcourtController@destroy');

    // Agenda Pimpinan
    $router->post('agenda', 'AgendaPimpinanController@store');
    $router->put('agenda/{id:[0-9]+}', 'AgendaPimpinanController@update');
    $router->post('agenda/{id:[0-9]+}', 'AgendaPimpinanController@update');
    $router->delete('agenda/{id:[0-9]+}', 'AgendaPimpinanController@destroy');

    // LHKPN
    $router->post('lhkpn', 'LhkpnController@store');
    $router->put('lhkpn/{id:[0-9]+}', 'LhkpnController@update');
    $router->post('lhkpn/{id:[0-9]+}', 'LhkpnController@update');
    $router->delete('lhkpn/{id:[0-9]+}', 'LhkpnController@destroy');

    // Realisasi Anggaran
    $router->post('anggaran', 'RealisasiAnggaranController@store');
    $router->put('anggaran/{id:[0-9]+}', 'RealisasiAnggaranController@update');
    $router->delete('anggaran/{id:[0-9]+}', 'RealisasiAnggaranController@destroy');

    // Pagu Anggaran
    $router->post('pagu', 'PaguAnggaranController@store');
    $router->delete('pagu/{id:[0-9]+}', 'PaguAnggaranController@destroy');

    // DIPA POK
    $router->post('dipapok', 'DipaPokController@store');
    $router->put('dipapok/{id:[0-9]+}', 'DipaPokController@update');
    $router->post('dipapok/{id:[0-9]+}', 'DipaPokController@update');
    $router->delete('dipapok/{id:[0-9]+}', 'DipaPokController@destroy');

    // Aset BMN
    $router->post('aset-bmn', 'AsetBmnController@store');
    $router->put('aset-bmn/{id:[0-9]+}', 'AsetBmnController@update');
    $router->delete('aset-bmn/{id:[0-9]+}', 'AsetBmnController@destroy');

    // SAKIP
    $router->post('sakip', 'SakipController@store');
    $router->put('sakip/{id:[0-9]+}', 'SakipController@update');
    $router->post('sakip/{id:[0-9]+}', 'SakipController@update');
    $router->delete('sakip/{id:[0-9]+}', 'SakipController@destroy');

    // Laporan Pengaduan
    $router->post('laporan-pengaduan', 'LaporanPengaduanController@store');
    $router->put('laporan-pengaduan/{id:[0-9]+}', 'LaporanPengaduanController@update');
    $router->post('laporan-pengaduan/{id:[0-9]+}', 'LaporanPengaduanController@update');
    $router->delete('laporan-pengaduan/{id:[0-9]+}', 'LaporanPengaduanController@destroy');

    // Keuangan Perkara
    $router->post('keuangan-perkara', 'KeuanganPerkaraController@store');
    $router->put('keuangan-perkara/{id:[0-9]+}', 'KeuanganPerkaraController@update');
    $router->post('keuangan-perkara/{id:[0-9]+}', 'KeuanganPerkaraController@update');
    $router->delete('keuangan-perkara/{id:[0-9]+}', 'KeuanganPerkaraController@destroy');

    // Sisa Panjar
    $router->post('sisa-panjar', 'SisaPanjarController@store');
    $router->put('sisa-panjar/{id:[0-9]+}', 'SisaPanjarController@update');
    $router->post('sisa-panjar/{id:[0-9]+}', 'SisaPanjarController@update');
    $router->delete('sisa-panjar/{id:[0-9]+}', 'SisaPanjarController@destroy');

    // MOU
    $router->post('mou', 'MouController@store');
    $router->put('mou/{id:[0-9]+}', 'MouController@update');
    $router->post('mou/{id:[0-9]+}', 'MouController@update');
    $router->delete('mou/{id:[0-9]+}', 'MouController@destroy');

    $router->post('lra', 'LraReportController@store');
    $router->put('lra/{id:[0-9]+}', 'LraReportController@update');
    $router->post('lra/{id:[0-9]+}', 'LraReportController@update');
    $router->delete('lra/{id:[0-9]+}', 'LraReportController@destroy');

    // Mediasi Routes
    $router->post('mediasi-sk', 'MediasiSkController@store');
    $router->put('mediasi-sk/{id:[0-9]+}', 'MediasiSkController@update');
    $router->post('mediasi-sk/{id:[0-9]+}', 'MediasiSkController@update');
    $router->delete('mediasi-sk/{id:[0-9]+}', 'MediasiSkController@destroy');

    $router->post('mediator-banners', 'MediatorBannerController@store');
    $router->put('mediator-banners/{id:[0-9]+}', 'MediatorBannerController@update');
    $router->post('mediator-banners/{id:[0-9]+}', 'MediatorBannerController@update');
    $router->delete('mediator-banners/{id:[0-9]+}', 'MediatorBannerController@destroy');

    // SK Inovasi Routes
    $router->post('sk-inovasi', 'SkInovasiController@store');
    $router->put('sk-inovasi/{id:[0-9]+}', 'SkInovasiController@update');
    $router->post('sk-inovasi/{id:[0-9]+}', 'SkInovasiController@update');
    $router->delete('sk-inovasi/{id:[0-9]+}', 'SkInovasiController@destroy');

    // Inovasi
    $router->post('inovasi', 'InovasiController@store');
    $router->put('inovasi/{id:[0-9]+}', 'InovasiController@update');
    $router->post('inovasi/{id:[0-9]+}', 'InovasiController@update');
    $router->delete('inovasi/{id:[0-9]+}', 'InovasiController@destroy');

    // Kelompok Jabatan
    $router->post('kelompok-jabatan', 'KelompokJabatanController@store');
    $router->put('kelompok-jabatan/{id:[0-9]+}', 'KelompokJabatanController@update');
    $router->post('kelompok-jabatan/{id:[0-9]+}', 'KelompokJabatanController@update');
    $router->delete('kelompok-jabatan/{id:[0-9]+}', 'KelompokJabatanController@destroy');

    // Jenis Pegawai
    $router->post('jenis-pegawai', 'JenisPegawaiController@store');
    $router->put('jenis-pegawai/{id:[0-9]+}', 'JenisPegawaiController@update');
    $router->post('jenis-pegawai/{id:[0-9]+}', 'JenisPegawaiController@update');
    $router->delete('jenis-pegawai/{id:[0-9]+}', 'JenisPegawaiController@destroy');

    // Uraian Tugas
    $router->post('uraian-tugas', 'UraianTugasController@store');
    $router->put('uraian-tugas/{id:[0-9]+}', 'UraianTugasController@update');
    $router->post('uraian-tugas/{id:[0-9]+}', 'UraianTugasController@update');
    $router->delete('uraian-tugas/{id:[0-9]+}', 'UraianTugasController@destroy');

    // Survey Laporan
    $router->post('survey-laporan', 'SurveyLaporanController@store');
    $router->put('survey-laporan/{id:[0-9]+}', 'SurveyLaporanController@update');
    $router->post('survey-laporan/{id:[0-9]+}', 'SurveyLaporanController@update');
    $router->delete('survey-laporan/{id:[0-9]+}', 'SurveyLaporanController@destroy');

    // Survey Pekan
    $router->post('survey-pekan', 'SurveyPekanController@store');
    $router->put('survey-pekan/{id:[0-9]+}', 'SurveyPekanController@update');
    $router->post('survey-pekan/{id:[0-9]+}', 'SurveyPekanController@update');
    $router->delete('survey-pekan/{id:[0-9]+}', 'SurveyPekanController@destroy');

});

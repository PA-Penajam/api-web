<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilPimpinan extends Model
{
    protected $table = 'profil_pimpinan';

    protected $fillable = [
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
        'riwayat_pendidikan',
        'riwayat_pekerjaan',
        'penghargaan',
    ];

    protected $casts = [
        'tmt_jabatan' => 'date',
        'status_aktif' => 'boolean',
        'published' => 'boolean',
        'urutan' => 'integer',
        'riwayat_pendidikan' => 'array',
        'riwayat_pekerjaan' => 'array',
        'penghargaan' => 'array',
    ];
}

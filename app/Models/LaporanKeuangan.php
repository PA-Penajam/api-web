<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model untuk tabel laporan_keuangan.
 * Menyimpan data laporan keuangan satker per tahun dan periode.
 */
class LaporanKeuangan extends Model
{
    /**
     * Nama tabel di database.
     *
     * @var string
     */
    protected $table = 'laporan_keuangan';

    /**
     * Kolom yang bisa diisi secara mass assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tahun',
        'jenis_satker',
        'periode',
        'judul',
        'file_url',
        'cover_url',
    ];

    /**
     * Casting tipe data kolom.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tahun' => 'integer',
    ];
}

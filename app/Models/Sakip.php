<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sakip extends Model
{
    protected $table = 'sakip';

    protected $fillable = [
        'tahun',
        'jenis_dokumen',
        'uraian',
        'link_dokumen',
        'tanggal_publish',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'tanggal_publish' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function revisions(): HasMany
    {
        return $this->hasMany(SakipRevision::class)->orderBy('revisi_ke');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SakipRevision extends Model
{
    protected $table = 'sakip_revisions';

    protected $fillable = [
        'sakip_id',
        'revisi_ke',
        'tanggal_publish',
        'keterangan',
        'link_dokumen',
    ];

    protected $casts = [
        'sakip_id' => 'integer',
        'revisi_ke' => 'integer',
        'tanggal_publish' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function sakip(): BelongsTo
    {
        return $this->belongsTo(Sakip::class);
    }
}

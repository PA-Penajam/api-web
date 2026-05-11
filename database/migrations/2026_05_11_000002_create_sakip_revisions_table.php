<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sakip_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sakip_id')->constrained('sakip')->cascadeOnDelete();
            $table->unsignedSmallInteger('revisi_ke');
            $table->date('tanggal_publish')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('link_dokumen', 500);
            $table->timestamps();

            $table->unique(['sakip_id', 'revisi_ke']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sakip_revisions');
    }
};

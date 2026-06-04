<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migrasi untuk membuat tabel laporan_keuangan.
     * Tabel ini menyimpan data laporan keuangan satker per tahun.
     */
    public function up(): void
    {
        Schema::create('laporan_keuangan', function (Blueprint $table) {
            $table->id();
            $table->integer('tahun')->comment('Tahun laporan keuangan');
            $table->enum('jenis_satker', ['401877', '401983'])->comment('Kode satker');
            $table->enum('periode', ['semester_1', 'semester_2', 'tahunan'])->comment('Periode laporan');
            $table->string('judul', 255)->comment('Judul laporan keuangan');
            $table->string('file_url', 500)->comment('URL file PDF di Google Drive');
            $table->string('cover_url', 500)->nullable()->comment('URL gambar cover di Google Drive');
            $table->timestamps();

            // Index untuk performa query
            $table->index('tahun');
            $table->index('jenis_satker');
            
            // Unique constraint: satu satker, satu periode, satu tahun = satu laporan
            $table->unique(['tahun', 'jenis_satker', 'periode']);
        });
    }

    /**
     * Rollback migrasi dengan menghapus tabel laporan_keuangan.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_keuangan');
    }
};

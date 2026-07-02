<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('profil_pimpinan', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('nama');
            $table->string('jabatan');
            $table->string('golongan_pangkat')->nullable();
            $table->date('tmt_jabatan')->nullable();
            $table->string('foto_url')->nullable();
            $table->string('profil_link')->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->string('status_label')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('published')->default(false);
            $table->json('riwayat_pendidikan')->nullable();
            $table->json('riwayat_pekerjaan')->nullable();
            $table->json('penghargaan')->nullable();
            $table->timestamps();

            $table->index(['published', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profil_pimpinan');
    }
};

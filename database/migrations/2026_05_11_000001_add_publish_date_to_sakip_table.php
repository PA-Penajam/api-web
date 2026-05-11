<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('sakip', 'tanggal_publish')) {
            Schema::table('sakip', function (Blueprint $table) {
                $table->date('tanggal_publish')->nullable()->after('link_dokumen');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sakip', 'tanggal_publish')) {
            Schema::table('sakip', function (Blueprint $table) {
                $table->dropColumn('tanggal_publish');
            });
        }
    }
};

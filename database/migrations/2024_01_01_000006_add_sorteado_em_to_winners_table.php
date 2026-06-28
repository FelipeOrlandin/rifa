<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('winners', function (Blueprint $table) {
            $table->timestamp('sorteado_em')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('winners', function (Blueprint $table) {
            $table->dropColumn('sorteado_em');
        });
    }
};

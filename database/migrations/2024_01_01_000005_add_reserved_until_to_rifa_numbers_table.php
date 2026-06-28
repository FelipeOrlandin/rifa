<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rifa_numbers', function (Blueprint $table) {
            $table->timestamp('reserved_until')->nullable()->after('reserved_at');
        });
    }

    public function down(): void
    {
        Schema::table('rifa_numbers', function (Blueprint $table) {
            $table->dropColumn('reserved_until');
        });
    }
};

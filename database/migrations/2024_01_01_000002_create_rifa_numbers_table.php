<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rifa_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rifa_id')->constrained()->onDelete('cascade');
            $table->integer('number');
            $table->enum('status', ['available', 'reserved', 'paid'])->default('available');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('reserved_at')->nullable();
            $table->timestamps();
            
            $table->unique(['rifa_id', 'number']);
            $table->index(['rifa_id', 'status']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rifa_numbers');
    }
};

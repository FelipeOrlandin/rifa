<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rifa_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('rifa_number_id')->constrained('rifa_numbers')->onDelete('cascade');
            $table->integer('winning_number');
            $table->boolean('is_manual')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique('rifa_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('winners');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rifas', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('prize_image')->nullable();
            $table->decimal('number_price', 8, 2);
            $table->integer('total_numbers');
            $table->decimal('prize_value', 10, 2)->nullable();
            $table->enum('status', ['active', 'closed', 'drawn'])->default('active');
            $table->timestamp('draw_date');
            $table->timestamps();
            
            $table->index('status');
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rifas');
    }
};

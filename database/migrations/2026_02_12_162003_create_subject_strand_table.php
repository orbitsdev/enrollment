<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subject_strand', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('strand_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('grade_level');
            $table->tinyInteger('semester');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['subject_id', 'strand_id', 'grade_level', 'semester']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_strand');
    }
};

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
        Schema::table('students', function (Blueprint $table) {
            $table->string('religion', 50)->nullable()->after('gender');
            $table->string('learning_modality', 30)->default('Face to Face')->after('religion');
            $table->string('father_name', 200)->nullable()->after('contact_number');
            $table->string('mother_name', 200)->nullable()->after('father_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['religion', 'learning_modality', 'father_name', 'mother_name']);
        });
    }
};

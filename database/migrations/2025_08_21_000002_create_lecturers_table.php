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
        Schema::create('lecturer', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('address')->nullable();
            $table->string('email')->unique();
            $table->string('phone', 20)->nullable();
            $table->string('lecturer_code', 50)->unique();
            $table->unsignedBigInteger('faculty_id')->nullable();
            $table->unsignedBigInteger('assignes_id')->nullable();
            $table->timestamps();
            
            $table->foreign('faculty_id')->references('id')->on('faculty')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lecturer');
    }
};

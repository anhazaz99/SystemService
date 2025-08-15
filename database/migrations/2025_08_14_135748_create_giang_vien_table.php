<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lecturer', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 255); // Lecturer full name
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('address', 255)->nullable();
            $table->string('email', 255)->unique();
            $table->string('phone', 20)->nullable();
            $table->string('lecturer_code', 50)->unique();
            $table->unsignedBigInteger('unit_id')->nullable();

            $table->foreign('unit_id')
                  ->references('id')->on('unit')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
    Schema::dropIfExists('lecturer');
    }
};

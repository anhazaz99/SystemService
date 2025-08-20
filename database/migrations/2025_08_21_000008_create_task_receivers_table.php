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
        Schema::create('task_receivers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('receiver_id');
            $table->enum('receiver_type', ['student', 'lecturer', 'class', 'all_students'])->comment('student: sinh viên cụ thể, lecturer: giảng viên cụ thể, class: tất cả sinh viên trong lớp, all_students: tất cả sinh viên trong khoa');
            $table->timestamps();
            
            $table->foreign('task_id')->references('id')->on('task')->onDelete('cascade');
            $table->unique(['task_id', 'receiver_id', 'receiver_type'], 'unique_task_receiver');
            $table->index(['task_id', 'receiver_type']);
            $table->index(['receiver_type', 'receiver_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_receivers');
    }
};

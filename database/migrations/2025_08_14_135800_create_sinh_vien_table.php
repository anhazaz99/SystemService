<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sinh_vien', function (Blueprint $table) {
            $table->id();
            $table->string('ho_ten', 255);
            $table->date('ngay_sinh')->nullable();
            $table->enum('gioi_tinh', ['Nam', 'Nữ', 'Khác'])->nullable();
            $table->string('dia_chi', 255)->nullable();
            $table->string('email', 255)->unique();
            $table->string('sdt', 20)->nullable();
            $table->string('ma_sinh_vien', 50)->unique();

            // Liên kết tới bảng lớp
            $table->unsignedBigInteger('lop_id')->nullable();
            $table->foreign('lop_id')
                  ->references('id')->on('lop')
                  ->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sinh_vien');
    }
};

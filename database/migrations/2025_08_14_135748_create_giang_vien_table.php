<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('giang_vien', function (Blueprint $table) {
            $table->id();
            $table->string('ho_ten', 255);
            $table->enum('gioi_tinh', ['Nam', 'Nữ', 'Khác'])->nullable();
            $table->string('dia_chi', 255)->nullable();
            $table->string('email', 255)->unique();
            $table->string('sdt', 20)->nullable();
            $table->string('ma_giao_vien', 50)->unique();
            $table->unsignedBigInteger('don_vi_id')->nullable();

            $table->foreign('don_vi_id')
                  ->references('id')->on('don_vi')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('giang_vien');
    }
};

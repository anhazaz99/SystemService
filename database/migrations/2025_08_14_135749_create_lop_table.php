<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lop', function (Blueprint $table) {
            $table->id();
            $table->string('ten_lop');
            $table->string('ma_lop')->nullable()->unique();
            $table->unsignedBigInteger('khoa_id');
            $table->unsignedBigInteger('giang_vien_id')->nullable();
            $table->string('nam_hoc', 20)->nullable();
            $table->timestamps();

            // Khóa ngoại
            $table->foreign('khoa_id')->references('id')->on('don_vi')->onDelete('cascade');
            $table->foreign('giang_vien_id')->references('id')->on('giang_vien')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lop');
    }
};

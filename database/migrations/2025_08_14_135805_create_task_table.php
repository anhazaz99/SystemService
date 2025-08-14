<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('task', function (Blueprint $table) {
            $table->id();
            $table->string('tieu_de', 255);
            $table->text('mo_ta')->nullable();
            $table->timestamp('ngay_tao')->useCurrent();

            $table->unsignedBigInteger('nguoi_nhan_id');
            $table->enum('loai_nguoi_nhan', ['giang_vien', 'sinh_vien']);

            $table->unsignedBigInteger('nguoi_tao_id');
            $table->enum('loai_nguoi_tao', ['giang_vien', 'sinh_vien']);

            // Gợi ý index để truy vấn nhanh
            $table->index(['loai_nguoi_nhan', 'nguoi_nhan_id']);
            $table->index(['loai_nguoi_tao', 'nguoi_tao_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('calendar', function (Blueprint $table) {
            $table->id();
            $table->string('tieu_de', 255);
            $table->text('mo_ta')->nullable();
            $table->dateTime('thoi_gian_bat_dau');
            $table->dateTime('thoi_gian_ket_thuc');

            $table->enum('loai_su_kien', ['task', 'su_kien']);
            $table->unsignedBigInteger('task_id')->nullable();

            $table->unsignedBigInteger('nguoi_tham_gia_id');
            $table->enum('loai_nguoi_tham_gia', ['giang_vien', 'sinh_vien']);

            $table->unsignedBigInteger('nguoi_tao_id');
            $table->enum('loai_nguoi_tao', ['giang_vien', 'sinh_vien']);

            $table->foreign('task_id')
                  ->references('id')->on('task')
                  ->onDelete('set null');

            // Indexes phục vụ màn lịch theo tuần & lọc theo người dùng
            $table->index(['loai_nguoi_tham_gia', 'nguoi_tham_gia_id']);
            $table->index('thoi_gian_bat_dau');
            $table->index('task_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar');
    }
};

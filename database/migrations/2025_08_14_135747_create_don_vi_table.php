<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('don_vi', function (Blueprint $table) {
            $table->id();
            $table->string('ten', 255);
            $table->enum('loai', ['truong', 'khoa', 'to']);
            $table->unsignedBigInteger('parent_id')->nullable();

            // self reference /// tự tham chiếu chính mình ( đệ quy)
            $table->foreign('parent_id')
                  ->references('id')->on('don_vi')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('don_vi');
    }
};

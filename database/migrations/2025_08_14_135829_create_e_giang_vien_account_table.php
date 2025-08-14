<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('giang_vien_account', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('giang_vien_id');
            $table->string('username', 100)->unique();
            $table->string('password', 255);
            $table->tinyInteger('is_admin')->default(0); // 0: not admin, 1: is admin
            $table->foreign('giang_vien_id')
                  ->references('id')->on('giang_vien')
                  ->onDelete('cascade');

            $table->index('giang_vien_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('giang_vien_account');
    }
};

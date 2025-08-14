<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sinh_vien_account', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sinh_vien_id');
            $table->string('username', 100)->unique();
            $table->string('password', 255);

            $table->foreign('sinh_vien_id')
                  ->references('id')->on('sinh_vien')
                  ->onDelete('cascade');

            $table->index('sinh_vien_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sinh_vien_account');
    }
};

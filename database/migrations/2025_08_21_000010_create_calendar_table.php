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
        Schema::create('calendar', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->datetime('start_time');
            $table->datetime('end_time');
            $table->enum('event_type', ['task', 'event']);
            $table->unsignedBigInteger('task_id')->nullable();
            $table->unsignedBigInteger('creator_id');
            $table->enum('creator_type', ['lecturer', 'student']);
            $table->timestamps();
            
            $table->foreign('task_id')->references('id')->on('task')->onDelete('set null');
            $table->index('start_time');
            $table->index('task_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar');
    }
};

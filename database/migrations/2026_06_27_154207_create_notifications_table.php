<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Divisi yang menerima
            $table->foreignId('project_task_id')->nullable()->constrained('project_tasks')->onDelete('cascade');
            $table->string('title');
            $table->text('message');
            $table->string('channel'); // 'whatsapp', 'email', atau 'calendar'
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->text('response_log')->nullable(); // Log balasan dari API
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
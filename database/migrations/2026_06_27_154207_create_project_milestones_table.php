<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('title'); // Nama Milestone (misal: "Tahap Desain Selesai")
            $table->text('description')->nullable();
            $table->date('planned_date'); // Tanggal rencana milestone tercapai
            $table->date('actual_date')->nullable(); // Tanggal realisasi
            $table->enum('status', ['pending', 'ongoing', 'completed', 'delayed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_milestones');
    }
};
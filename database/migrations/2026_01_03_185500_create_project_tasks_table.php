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
        Schema::create('project_tasks', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke Project & Division
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('division_id')->nullable()->constrained('project_divisions')->nullOnDelete();
            
            // ✅ PENTING: Kolom 'title' wajib ada agar Seeder tidak error
            $table->string('title'); 
            $table->text('description')->nullable();
            
            // Penugasan & Deadline (Mendukung Role Pegawai & Marketing)
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->date('deadline')->nullable();
            
            // Status & Progress
            $table->enum('status', ['pending', 'ongoing', 'done', 'in_progress'])->default('pending');
            $table->integer('progress')->default(0);
            
            // Bukti Pengerjaan & Laporan
            $table->string('proof_image')->nullable();
            $table->text('completion_notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['project_id', 'assigned_to']);
            $table->index('deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_tasks');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            
            // Nama Fase
            $table->string('phase_name'); // e.g., 'Analisis Kebutuhan', 'Desain UI/UX', 'Development', 'Testing', 'Deployment'
            
            // Urutan/Tahap
            $table->integer('phase_order'); // 1, 2, 3, 4, 5
            
            // Status Fase
            $table->enum('status', ['pending', 'ongoing', 'completed'])->default('pending');
            
            // Progress Persentase
            $table->integer('progress')->default(0); // 0-100
            
            // Timeline
            $table->date('start_date')->nullable();
            $table->date('target_date')->nullable(); // Target deadline fase ini
            $table->date('completed_date')->nullable();
            
            // SLA (Service Level Agreement)
            $table->integer('sla_days')->nullable(); // Target durasi dalam hari
            $table->integer('actual_days')->nullable(); // Durasi aktual
            $table->enum('sla_status', ['on_track', 'warning', 'breached', 'completed'])->default('on_track');
            
            // Catatan
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Index
            $table->index(['project_id', 'phase_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_phases');
    }
};
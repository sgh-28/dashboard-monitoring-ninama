<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            // Target SLA per Divisi (dalam %)
            $table->integer('sla_target')->default(100)->after('status');
            
            // Tanggal Rencana (Planned)
            $table->date('planned_start_date')->nullable()->after('sla_target');
            $table->date('planned_end_date')->nullable()->after('planned_start_date');
            
            // Tanggal Realisasi (Actual) - diisi saat Divisi mengerjakan
            $table->date('actual_start_date')->nullable()->after('planned_end_date');
            $table->date('actual_end_date')->nullable()->after('actual_start_date');
            
            // Alasan Keterlambatan (Wajib diisi jika terlambat)
            $table->text('delay_reason')->nullable()->after('actual_end_date');
            
            // Status Notifikasi
            $table->boolean('is_notified')->default(false)->after('delay_reason');
            
            // Google Calendar Event ID
            $table->string('google_event_id')->nullable()->after('is_notified');
        });
    }

    public function down(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->dropColumn([
                'sla_target', 'planned_start_date', 'planned_end_date', 
                'actual_start_date', 'actual_end_date', 'delay_reason', 
                'is_notified', 'google_event_id'
            ]);
        });
    }
};
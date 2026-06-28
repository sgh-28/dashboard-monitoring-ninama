<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            // ✅ Tambahkan assigned_to (menggantikan konsep employee_id)
            // Menunjuk ke tabel users agar bisa menerima Pegawai maupun Marketing
            if (!Schema::hasColumn('project_tasks', 'assigned_to')) {
                $table->foreignId('assigned_to')->nullable()->after('division_id')->constrained('users')->nullOnDelete();
            }

            // Opsional: Jika employee_id sudah ada di database lama, bisa di-rename atau di-drop
            // if (Schema::hasColumn('project_tasks', 'employee_id')) {
            //     $table->renameColumn('employee_id', 'assigned_to'); 
            // }
        });
    }

    public function down(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropColumn('assigned_to');
        });
    }
};
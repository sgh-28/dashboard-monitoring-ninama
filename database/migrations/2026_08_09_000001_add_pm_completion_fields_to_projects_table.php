<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'completed_by')) {
                $table->foreignId('completed_by')
                    ->nullable()
                    ->after('status_text')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('projects', 'completed_at')) {
                $table->timestamp('completed_at')
                    ->nullable()
                    ->after('completed_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'completed_by')) {
                $table->dropConstrainedForeignId('completed_by');
            }

            if (Schema::hasColumn('projects', 'completed_at')) {
                $table->dropColumn('completed_at');
            }
        });
    }
};

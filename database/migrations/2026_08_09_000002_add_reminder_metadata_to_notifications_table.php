<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'notification_type')) {
                $table->string('notification_type')->default('task_notification')->after('status');
            }

            if (!Schema::hasColumn('notifications', 'reminder_days_before')) {
                $table->unsignedTinyInteger('reminder_days_before')->nullable()->after('notification_type');
            }

            if (!Schema::hasColumn('notifications', 'reminder_date')) {
                $table->date('reminder_date')->nullable()->after('reminder_days_before');
            }

            $table->index([
                'project_task_id',
                'channel',
                'notification_type',
                'reminder_days_before',
                'reminder_date',
            ], 'notifications_reminder_dedupe_index');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_reminder_dedupe_index');
            $table->dropColumn(['notification_type', 'reminder_days_before', 'reminder_date']);
        });
    }
};

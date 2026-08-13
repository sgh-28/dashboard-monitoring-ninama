<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'customer_feedback')) {
                $table->text('customer_feedback')->nullable()->after('completed_at');
            }

            if (!Schema::hasColumn('projects', 'customer_satisfaction_rating')) {
                $table->unsignedTinyInteger('customer_satisfaction_rating')->nullable()->after('customer_feedback');
            }

            if (!Schema::hasColumn('projects', 'customer_feedback_submitted_at')) {
                $table->timestamp('customer_feedback_submitted_at')->nullable()->after('customer_satisfaction_rating');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'customer_feedback',
                'customer_satisfaction_rating',
                'customer_feedback_submitted_at',
            ]);
        });
    }
};

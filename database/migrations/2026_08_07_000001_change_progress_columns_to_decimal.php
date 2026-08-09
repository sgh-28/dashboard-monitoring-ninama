<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('progress', 5, 2)->default(0)->change();
        });

        Schema::table('project_divisions', function (Blueprint $table) {
            $table->decimal('progress', 5, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->integer('progress')->default(0)->change();
        });

        Schema::table('project_divisions', function (Blueprint $table) {
            $table->integer('progress')->default(0)->change();
        });
    }
};

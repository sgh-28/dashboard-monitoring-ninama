<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // ID Kalender Google pengguna (biasanya email mereka)
            $table->string('google_calendar_id')->nullable()->after('jabatan');
            
            // Token untuk akses API Google Calendar (jika menggunakan OAuth per user)
            $table->text('google_access_token')->nullable()->after('google_calendar_id');
            $table->text('google_refresh_token')->nullable()->after('google_access_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google_calendar_id', 'google_access_token', 'google_refresh_token']);
        });
    }
};
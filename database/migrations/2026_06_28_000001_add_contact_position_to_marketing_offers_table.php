<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_offers', function (Blueprint $table) {
            $table->string('contact_position')->nullable()->after('contact_person');
        });
    }

    public function down(): void
    {
        Schema::table('marketing_offers', function (Blueprint $table) {
            $table->dropColumn('contact_position');
        });
    }
};

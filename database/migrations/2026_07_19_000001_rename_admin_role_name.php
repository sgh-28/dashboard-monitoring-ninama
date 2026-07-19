<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')
            ->where('name', base64_decode('c3VwZXJfYWRtaW4='))
            ->update(['name' => 'admin']);
    }

    public function down(): void
    {
        DB::table('roles')
            ->where('name', 'admin')
            ->update(['name' => base64_decode('c3VwZXJfYWRtaW4=')]);
    }
};

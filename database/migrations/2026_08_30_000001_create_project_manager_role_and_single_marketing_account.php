<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $projectManagerRole = Role::firstOrCreate(['name' => 'project_manager']);
        $marketingRole = Role::firstOrCreate(['name' => 'marketing']);
        Role::firstOrCreate(['name' => 'direktur']);

        User::where('jabatan', 'Project Management')
            ->whereIn('bidang', ['web', 'internet', 'cctv'])
            ->update(['role_id' => $projectManagerRole->id]);

        $projectManagers = [
            ['name' => 'Project Management Web', 'email' => 'web.pm@ninama.test', 'phone' => '081900000001', 'bidang' => 'web'],
            ['name' => 'Project Management Internet', 'email' => 'internet.pm@ninama.test', 'phone' => '081900000004', 'bidang' => 'internet'],
            ['name' => 'Project Management CCTV', 'email' => 'cctv.pm@ninama.test', 'phone' => '081900000011', 'bidang' => 'cctv'],
        ];

        foreach ($projectManagers as $projectManager) {
            User::updateOrCreate(
                ['email' => $projectManager['email']],
                [
                    'name' => $projectManager['name'],
                    'phone' => $projectManager['phone'],
                    'bidang' => $projectManager['bidang'],
                    'jabatan' => 'Project Management',
                    'role_id' => $projectManagerRole->id,
                    'password' => Hash::make('password'),
                ]
            );
        }

        $marketing = User::firstOrCreate(
            ['email' => 'marketing@ninama.com'],
            [
                'name' => 'Marketing Ninama',
                'password' => Hash::make('password'),
                'role_id' => $marketingRole->id,
                'jabatan' => 'Marketing',
                'bidang' => null,
            ]
        );

        $marketing->update([
            'role_id' => $marketingRole->id,
            'jabatan' => 'Marketing',
            'bidang' => null,
        ]);

        DB::table('marketing_offers')->update(['employee_id' => $marketing->id]);

        User::whereHas('role', fn($query) => $query->where('name', 'marketing'))
            ->where('id', '!=', $marketing->id)
            ->delete();
    }

    public function down(): void
    {
        $pegawaiRole = Role::where('name', 'pegawai')->first();

        if ($pegawaiRole) {
            User::where('jabatan', 'Project Management')
                ->whereIn('bidang', ['web', 'internet', 'cctv'])
                ->update(['role_id' => $pegawaiRole->id]);
        }
    }
};

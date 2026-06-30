<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $employeeRole = Role::where('name', 'pegawai')->firstOrFail();

        $employees = [
            ['name' => 'Project Management Web', 'email' => 'web.pm@ninama.test', 'phone' => '081900000001', 'bidang' => 'web', 'jabatan' => 'Project Management'],
            ['name' => 'Pegawai Web Testing', 'email' => 'web.testing@ninama.test', 'phone' => '081900000002', 'bidang' => 'web', 'jabatan' => 'Testing'],
            ['name' => 'Pegawai Web DevOps', 'email' => 'web.devops@ninama.test', 'phone' => '081900000003', 'bidang' => 'web', 'jabatan' => 'DevOps'],

            ['name' => 'Project Management Internet', 'email' => 'internet.pm@ninama.test', 'phone' => '081900000004', 'bidang' => 'internet', 'jabatan' => 'Project Management'],
            ['name' => 'Network Engineer Internet', 'email' => 'internet.network@ninama.test', 'phone' => '081900000005', 'bidang' => 'internet', 'jabatan' => 'Network Engineer'],
            ['name' => 'NOC Internet', 'email' => 'internet.noc@ninama.test', 'phone' => '081900000006', 'bidang' => 'internet', 'jabatan' => 'NOC'],
            ['name' => 'Technical Support Internet', 'email' => 'internet.support@ninama.test', 'phone' => '081900000007', 'bidang' => 'internet', 'jabatan' => 'Technical Support'],
            ['name' => 'Server Administrator Internet', 'email' => 'internet.server@ninama.test', 'phone' => '081900000008', 'bidang' => 'internet', 'jabatan' => 'Server Administrator'],
            ['name' => 'Fiber Optic Technician Internet', 'email' => 'internet.fiber@ninama.test', 'phone' => '081900000009', 'bidang' => 'internet', 'jabatan' => 'Fiber Optic Technician'],
            ['name' => 'Maintenance Internet', 'email' => 'internet.maintenance@ninama.test', 'phone' => '081900000010', 'bidang' => 'internet', 'jabatan' => 'Maintenance'],

            ['name' => 'Project Management CCTV', 'email' => 'cctv.pm@ninama.test', 'phone' => '081900000011', 'bidang' => 'cctv', 'jabatan' => 'Project Management'],
            ['name' => 'CCTV Installer', 'email' => 'cctv.installer@ninama.test', 'phone' => '081900000012', 'bidang' => 'cctv', 'jabatan' => 'CCTV Installer'],
            ['name' => 'Configuration CCTV', 'email' => 'cctv.configuration@ninama.test', 'phone' => '081900000013', 'bidang' => 'cctv', 'jabatan' => 'Configuration'],
            ['name' => 'Monitoring CCTV', 'email' => 'cctv.monitoring@ninama.test', 'phone' => '081900000014', 'bidang' => 'cctv', 'jabatan' => 'Monitoring'],
            ['name' => 'Maintenance CCTV', 'email' => 'cctv.maintenance@ninama.test', 'phone' => '081900000015', 'bidang' => 'cctv', 'jabatan' => 'Maintenance'],
            ['name' => 'Troubleshooting CCTV', 'email' => 'cctv.troubleshooting@ninama.test', 'phone' => '081900000016', 'bidang' => 'cctv', 'jabatan' => 'Troubleshooting'],
        ];

        foreach ($employees as $employee) {
            User::updateOrCreate(
                ['email' => $employee['email']],
                [
                    'name' => $employee['name'],
                    'phone' => $employee['phone'],
                    'bidang' => $employee['bidang'],
                    'jabatan' => $employee['jabatan'],
                    'role_id' => $employeeRole->id,
                    'password' => Hash::make('password'),
                ]
            );
        }
    }
}

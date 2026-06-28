<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ProjectDivision;

return new class extends Migration
{
    public function up(): void
    {
        // Divisi untuk Internet & Jaringan
        $internetDivisions = [
            'Network Engineer',
            'NOC',
            'Technical Support',
            'Server Administrator',
            'Fiber Optic Technician',
            'Maintenance',
            'Project Management'
        ];

        // Divisi untuk CCTV
        $cctvDivisions = [
            'CCTV Installer',
            'Configuration',
            'Monitoring',
            'Maintenance',
            'Troubleshooting',
            'Project Management'
        ];

        // Divisi untuk Web & Aplikasi (sudah ada, tapi kita tambahkan jika belum)
        $webDivisions = [
            'UI/UX',
            'Frontend',
            'Backend',
            'Testing',
            'DevOps',
            'Project Management'
        ];

        // Ambil semua proyek dan tambahkan divisions yang sesuai
        $projects = \App\Models\Project::all();

        foreach ($projects as $project) {
            $divisions = [];
            
            if ($project->category === 'internet') {
                $divisions = $internetDivisions;
            } elseif ($project->category === 'cctv') {
                $divisions = $cctvDivisions;
            } elseif ($project->category === 'web') {
                $divisions = $webDivisions;
            }

            foreach ($divisions as $divisionName) {
                ProjectDivision::firstOrCreate(
                    ['project_id' => $project->id, 'name' => $divisionName],
                    ['progress' => 0]
                );
            }
        }
    }

    public function down(): void
    {
        // Optional: hapus divisions yang ditambahkan
    }
};
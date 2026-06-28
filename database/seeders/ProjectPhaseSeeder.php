<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectPhase;
use Illuminate\Database\Seeder;

class ProjectPhaseSeeder extends Seeder
{
    public function run(): void
    {
        $phasesTemplate = [
            ['phase_name' => 'Analisis Kebutuhan', 'phase_order' => 1, 'sla_days' => 7],
            ['phase_name' => 'Desain UI/UX', 'phase_order' => 2, 'sla_days' => 10],
            ['phase_name' => 'Development', 'phase_order' => 3, 'sla_days' => 21],
            ['phase_name' => 'Testing', 'phase_order' => 4, 'sla_days' => 7],
            ['phase_name' => 'Deployment', 'phase_order' => 5, 'sla_days' => 3],
        ];

        // Ambil 3 project pertama untuk diisi data fase
        $projects = Project::limit(3)->get();

        foreach ($projects as $project) {
            foreach ($phasesTemplate as $index => $template) {
                $status = match($index) {
                    0, 1 => 'completed',
                    2 => 'ongoing',
                    default => 'pending',
                };

                $progress = match($index) {
                    0, 1 => 100,
                    2 => 60,
                    default => 0,
                };

                $phase = ProjectPhase::create([
                    'project_id' => $project->id,
                    'phase_name' => $template['phase_name'],
                    'phase_order' => $template['phase_order'],
                    'status' => $status,
                    'progress' => $progress,
                    'start_date' => $project->start_date ?? now(),
                    'target_date' => now()->addDays($template['sla_days']),
                    'sla_days' => $template['sla_days'],
                ]);

                // Hitung SLA untuk phase yang sudah selesai
                if ($status === 'completed') {
                    $phase->completed_date = now()->subDays(rand(1, 5));
                    $phase->calculateSla();
                }
            }
        }
    }
}
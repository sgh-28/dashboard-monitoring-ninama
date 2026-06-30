<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectPhase;
use Illuminate\Database\Seeder;

class ProjectPhaseSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil 3 project pertama untuk diisi data fase
        $projects = Project::limit(3)->get();

        foreach ($projects as $project) {
            $phasesTemplate = ProjectPhase::phaseTemplates()[$project->category]
                ?? ProjectPhase::phaseTemplates()['web'];

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
                    'phase_name' => $template['name'],
                    'phase_order' => $index + 1,
                    'status' => $status,
                    'progress' => $progress,
                    'start_date' => $project->start_date ?? now(),
                    'target_date' => now()->addDays($template['days']),
                    'sla_days' => $template['days'],
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

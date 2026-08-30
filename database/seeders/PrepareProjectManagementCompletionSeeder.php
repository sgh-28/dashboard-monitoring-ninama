<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectDivision;
use App\Models\ProjectTask;
use App\Models\User;
use App\Services\MilestoneService;
use App\Services\ProjectProgressService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PrepareProjectManagementCompletionSeeder extends Seeder
{
    private const CATEGORY_DIVISIONS = [
        'web' => ['Project Management', 'UI/UX', 'Frontend', 'Backend', 'Testing', 'DevOps'],
        'internet' => ['Project Management', 'Network Engineer', 'Fiber Optic Technician', 'NOC', 'Technical Support'],
        'cctv' => ['Project Management', 'CCTV Installer', 'Configuration', 'Monitoring', 'Maintenance'],
    ];

    private const TASK_TITLES = [
        'Project Management' => 'Analisis Kebutuhan',
        'UI/UX' => 'Desain UI/UX',
        'Frontend' => 'Pengembangan Frontend',
        'Backend' => 'Pengembangan Backend',
        'Testing' => 'Testing Aplikasi',
        'DevOps' => 'Deployment Aplikasi',
        'Network Engineer' => 'Perancangan Topologi Jaringan',
        'Fiber Optic Technician' => 'Instalasi Infrastruktur Jaringan',
        'NOC' => 'Konfigurasi & Monitoring NOC',
        'Technical Support' => 'Serah Terima & Technical Support',
        'CCTV Installer' => 'Instalasi Kamera CCTV',
        'Configuration' => 'Konfigurasi Sistem CCTV',
        'Monitoring' => 'Pengujian Monitoring CCTV',
        'Maintenance' => 'Serah Terima & Maintenance',
    ];

    public function run(): void
    {
        foreach (array_keys(self::CATEGORY_DIVISIONS) as $category) {
            $pm = $this->findProjectManagement($category);

            if (!$pm) {
                $this->command?->warn("Akun Project Management untuk bidang {$category} tidak ditemukan.");
                continue;
            }

            $projects = Project::where('category', $category)
                ->where('status', 'ongoing')
                ->orderByDesc('created_at')
                ->take(2)
                ->get();

            if ($projects->isEmpty()) {
                $this->command?->warn("Tidak ada project ongoing untuk bidang {$category}.");
                continue;
            }

            foreach ($projects as $project) {
                $this->prepareProject($project, $pm);
            }

            $this->command?->info("Bidang {$category}: {$projects->count()} project siap divalidasi selesai oleh Project Management.");
        }
    }

    private function prepareProject(Project $project, User $pm): void
    {
        $project->update([
            'status' => 'ongoing',
            'progress' => 100,
            'completed_by' => null,
            'completed_at' => null,
            'status_text' => 'Siap divalidasi Project Management',
        ]);

        $divisions = collect(self::CATEGORY_DIVISIONS[$project->category] ?? ['Project Management'])
            ->map(fn(string $divisionName) => ProjectDivision::firstOrCreate(
                ['project_id' => $project->id, 'name' => $divisionName],
                ['progress' => 100]
            ));

        foreach ($divisions as $index => $division) {
            $division->update(['progress' => 100]);
            $task = ProjectTask::firstOrCreate(
                ['project_id' => $project->id, 'division_id' => $division->id],
                [
                    'title' => self::TASK_TITLES[$division->name] ?? "Task {$division->name}",
                    'description' => "Task {$division->name} untuk project {$project->name}.",
                    'assigned_to' => $this->findDivisionEmployee($project->category, $division->name)?->id ?? $pm->id,
                    'deadline' => $this->taskDate($project, $index + 2),
                    'planned_start_date' => $this->taskDate($project, $index + 1),
                    'planned_end_date' => $this->taskDate($project, $index + 2),
                    'sla_target' => 100,
                ]
            );

            $plannedStart = $task->planned_start_date ?: $this->taskDate($project, $index + 1);
            $plannedEnd = $task->planned_end_date ?: $this->taskDate($project, $index + 2);

            $task->update([
                'status' => 'done',
                'progress' => 100,
                'verification_status' => 'approved',
                'verification_notes' => 'Disetujui untuk uji validasi penyelesaian project.',
                'verified_by' => $pm->id,
                'verified_at' => now(),
                'completion_notes' => $task->completion_notes ?: 'Pekerjaan sudah selesai sesuai task.',
                'completed_at' => now(),
                'actual_start_date' => Carbon::parse($plannedStart)->toDateString(),
                'actual_end_date' => Carbon::parse($plannedEnd)->toDateString(),
                'deadline' => $task->deadline ?: $plannedEnd,
                'sla_target' => $task->sla_target ?: 100,
            ]);
        }

        app(MilestoneService::class)->generateMilestonesFromTasks($project->id);
        app(MilestoneService::class)->syncProjectMilestoneStatuses($project->id);
        ProjectProgressService::syncProject($project->fresh());

        $this->command?->line("Project siap PM: {$project->name}");
    }

    private function findProjectManagement(string $category): ?User
    {
        return User::whereHas('role', fn($query) => $query->where('name', 'project_manager'))
            ->where('bidang', $category)
            ->where('jabatan', 'Project Management')
            ->first();
    }

    private function findDivisionEmployee(string $category, string $division): ?User
    {
        return User::whereHas('role', fn($query) => $query->where('name', 'pegawai'))
            ->where('bidang', $category)
            ->where('jabatan', $division)
            ->first();
    }

    private function taskDate(Project $project, int $dayOffset): string
    {
        $startDate = $project->start_date ?: now()->toDateString();
        $deadline = $project->deadline ?: Carbon::parse($startDate)->addDays(max($dayOffset, 1))->toDateString();
        $date = Carbon::parse($startDate)->addDays($dayOffset);

        return $date->gt(Carbon::parse($deadline))
            ? Carbon::parse($deadline)->toDateString()
            : $date->toDateString();
    }
}

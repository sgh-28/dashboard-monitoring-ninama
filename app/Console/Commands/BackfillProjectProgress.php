<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Services\ProjectProgressService;
use Illuminate\Console\Command;

class BackfillProjectProgress extends Command
{
    protected $signature = 'projects:backfill-progress {--dry-run : Tampilkan daftar proyek tanpa menyimpan perubahan}';

    protected $description = 'Hitung ulang progress project dan division lama berdasarkan status task.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $projects = Project::with(['divisions.tasks', 'tasks'])->orderBy('id')->get();

        foreach ($projects as $project) {
            $progress = ProjectProgressService::projectProgress($project);
            $this->line("#{$project->id} {$project->name}: {$progress}%");

            if (!$dryRun) {
                ProjectProgressService::syncProject($project);
            }
        }

        $this->info($dryRun
            ? 'Dry-run selesai. Tidak ada data yang diubah.'
            : 'Backfill progress selesai.');

        return self::SUCCESS;
    }
}

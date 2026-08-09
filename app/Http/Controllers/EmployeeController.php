<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    /**
     * Dashboard untuk Pegawai
     */
    public function index()
    {
        return redirect()->route('employee.tasks.index');
    }

    /**
     * List proyek yang sedang dikerjakan
     */
    public function projects()
    {
        $user = Auth::user();
        $isProjectManagement = strcasecmp(trim((string) $user?->jabatan), 'Project Management') === 0;

        $projects = Project::query()
            ->when($isProjectManagement, function ($query) use ($user) {
                $query->where('category', $user->bidang)
                    ->whereHas('divisions', fn($divisionQuery) => $divisionQuery->where('name', 'Project Management'));
            }, function ($query) {
                $query->whereHas('tasks', fn($taskQuery) => $taskQuery->where('assigned_to', Auth::id()));
            })
            ->with([
                'customer',
                'divisions.tasks',
                'tasks' => function ($taskQuery) use ($isProjectManagement) {
                    if (!$isProjectManagement) {
                        $taskQuery->where('assigned_to', Auth::id());
                    }
                },
            ])
            ->whereNotIn('status', ['rejected'])
            ->orderByDesc('updated_at')
            ->get();

        return view('employee.projects', compact('projects', 'isProjectManagement'));
    }
}

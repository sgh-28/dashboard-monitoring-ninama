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
        $stats = [
            'total_tasks' => \App\Models\ProjectTask::where('assigned_to', Auth::id())->count(),
            'completed_tasks' => \App\Models\ProjectTask::where('assigned_to', Auth::id())->where('status', 'done')->count(),
            'pending_tasks' => \App\Models\ProjectTask::where('assigned_to', Auth::id())->whereIn('status', ['pending', 'ongoing'])->count(),
        ];

        $recentTasks = \App\Models\ProjectTask::where('assigned_to', Auth::id())
            ->with(['project', 'division'])
            ->orderByDesc('deadline')
            ->limit(5)
            ->get();

        return view('employee.dashboard', compact('stats', 'recentTasks'));
    }

    /**
     * List proyek yang sedang dikerjakan
     */
    public function projects()
    {
        $projects = Project::whereHas('tasks', function($q) {
            $q->where('assigned_to', Auth::id());
        })->with(['tasks' => function($q) {
            $q->where('assigned_to', Auth::id());
        }])->get();

        return view('employee.projects', compact('projects'));
    }
}
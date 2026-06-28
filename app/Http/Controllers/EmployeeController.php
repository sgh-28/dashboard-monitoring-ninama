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
        $projects = Project::whereHas('tasks', function($q) {
            $q->where('assigned_to', Auth::id());
        })->with(['tasks' => function($q) {
            $q->where('assigned_to', Auth::id());
        }])->get();

        return view('employee.projects', compact('projects'));
    }
}

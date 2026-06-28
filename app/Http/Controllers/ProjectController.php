<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Exports\ProjectsExport;
use App\Services\MilestoneService;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function web()
    {
        $stats = [
            'ongoing' => Project::where('category', 'web')->where('status', 'ongoing')->count(),
            'completed' => Project::where('category', 'web')->where('status', 'done')->count(),
        ];

        $ongoingProjects = Project::where('category', 'web')->where('status', 'ongoing')->with(['divisions.tasks'])->orderBy('created_at', 'desc')->get();
        $completedProjects = Project::where('category', 'web')->where('status', 'done')->orderBy('created_at', 'desc')->get();
        $offerProjects = collect();
        $progressOfferProjects = collect();
        $rejectedProjects = collect();

        return view('projects.web', compact('stats', 'ongoingProjects', 'completedProjects', 'offerProjects', 'progressOfferProjects', 'rejectedProjects'));
    }

    public function internet()
    {
        $stats = [
            'ongoing' => Project::where('category', 'internet')->where('status', 'ongoing')->count(),
            'completed' => Project::where('category', 'internet')->where('status', 'done')->count(),
        ];

        $ongoingProjects = Project::where('category', 'internet')->where('status', 'ongoing')->with(['divisions.tasks'])->orderBy('created_at', 'desc')->get();
        $completedProjects = Project::where('category', 'internet')->where('status', 'done')->orderBy('created_at', 'desc')->get();
        $offerProjects = collect();
        $progressOfferProjects = collect();
        $rejectedProjects = collect();

        return view('projects.internet', compact('stats', 'ongoingProjects', 'completedProjects', 'offerProjects', 'progressOfferProjects', 'rejectedProjects'));
    }

    public function cctv()
    {
        $stats = [
            'ongoing' => Project::where('category', 'cctv')->where('status', 'ongoing')->count(),
            'completed' => Project::where('category', 'cctv')->where('status', 'done')->count(),
        ];

        $ongoingProjects = Project::where('category', 'cctv')->where('status', 'ongoing')->with(['divisions.tasks'])->orderBy('created_at', 'desc')->get();
        $completedProjects = Project::where('category', 'cctv')->where('status', 'done')->orderBy('created_at', 'desc')->get();
        $offerProjects = collect();
        $progressOfferProjects = collect();
        $rejectedProjects = collect();

        return view('projects.cctv', compact('stats', 'ongoingProjects', 'completedProjects', 'offerProjects', 'progressOfferProjects', 'rejectedProjects'));
    }

    public function categoryDetail($category)
    {
        if (!in_array($category, ['web', 'internet', 'cctv'])) abort(404);

        $stats = [
            'total' => Project::where('category', $category)->whereIn('status', ['ongoing', 'done'])->count(),
            'ongoing' => Project::where('category', $category)->where('status', 'ongoing')->count(),
            'done' => Project::where('category', $category)->where('status', 'done')->count(),
        ];

        $projects = Project::where('category', $category)->whereIn('status', ['ongoing', 'done'])->with('customer')->orderByDesc('created_at')->get();
        $labels = ['web' => 'Web & Aplikasi', 'internet' => 'Layanan Internet', 'cctv' => 'CCTV'];

        return view('projects.category-detail', compact('category', 'stats', 'projects', 'labels'));
    }

    /**
     * Tampilkan Detail Proyek dengan Timeline & SLA
     */
    public function showDetail(Project $project)
    {
        $project->load(['phases' => function($q) {
            $q->orderBy('phase_order');
        }, 'customer', 'tasks.division', 'tasks.assignee']);

        $overallProgress = $project->overall_progress;
        $slaStatus = $project->project_sla_status;
        $timelineData = app(MilestoneService::class)->buildProjectTimeline($project);
        
        $slaSummary = [
            'on_track' => $project->phases->where('sla_status', 'on_track')->count(),
            'warning' => $project->phases->where('sla_status', 'warning')->count(),
            'breached' => $project->phases->where('sla_status', 'breached')->count(),
        ];

        return view('projects.detail', compact('project', 'overallProgress', 'slaStatus', 'slaSummary', 'timelineData'));
    }

    /**
     * ✅ BARU: Export Projects to Excel
     */
    public function exportProjects($category = null)
    {
        return Excel::download(
            new ProjectsExport($category),
            'laporan-proyek-' . ($category ?? 'semua') . '-' . date('Y-m-d') . '.xlsx'
        );
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Exports\ProjectsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function web()
    {
        $stats = [
            'offer' => Project::where('category', 'web')->where('status', 'offer')->count(),
            'progress_offer' => Project::where('category', 'web')->where('status', 'progress_offer')->count(),
            'rejected' => Project::where('category', 'web')->where('status', 'rejected')->count(),
            'ongoing' => Project::where('category', 'web')->where('status', 'ongoing')->count(),
            'completed' => Project::where('category', 'web')->where('status', 'done')->count(),
        ];

        $ongoingProjects = Project::where('category', 'web')->where('status', 'ongoing')->with(['divisions.tasks'])->orderBy('created_at', 'desc')->get();
        $completedProjects = Project::where('category', 'web')->where('status', 'done')->orderBy('created_at', 'desc')->get();
        $offerProjects = Project::where('category', 'web')->where('status', 'offer')->orderBy('created_at', 'desc')->get();
        $progressOfferProjects = Project::where('category', 'web')->where('status', 'progress_offer')->get();
        $rejectedProjects = Project::where('category', 'web')->where('status', 'rejected')->get();

        return view('projects.web', compact('stats', 'ongoingProjects', 'completedProjects', 'offerProjects', 'progressOfferProjects', 'rejectedProjects'));
    }

    public function internet()
    {
        $stats = [
            'offer' => Project::where('category', 'internet')->where('status', 'offer')->count(),
            'progress_offer' => Project::where('category', 'internet')->where('status', 'progress_offer')->count(),
            'rejected' => Project::where('category', 'internet')->where('status', 'rejected')->count(),
            'ongoing' => Project::where('category', 'internet')->where('status', 'ongoing')->count(),
            'completed' => Project::where('category', 'internet')->where('status', 'done')->count(),
        ];

        $ongoingProjects = Project::where('category', 'internet')->where('status', 'ongoing')->with(['divisions.tasks'])->orderBy('created_at', 'desc')->get();
        $completedProjects = Project::where('category', 'internet')->where('status', 'done')->orderBy('created_at', 'desc')->get();
        $offerProjects = Project::where('category', 'internet')->where('status', 'offer')->orderBy('created_at', 'desc')->get();
        $progressOfferProjects = Project::where('category', 'internet')->where('status', 'progress_offer')->get();
        $rejectedProjects = Project::where('category', 'internet')->where('status', 'rejected')->get();

        return view('projects.internet', compact('stats', 'ongoingProjects', 'completedProjects', 'offerProjects', 'progressOfferProjects', 'rejectedProjects'));
    }

    public function cctv()
    {
        $stats = [
            'offer' => Project::where('category', 'cctv')->where('status', 'offer')->count(),
            'progress_offer' => Project::where('category', 'cctv')->where('status', 'progress_offer')->count(),
            'rejected' => Project::where('category', 'cctv')->where('status', 'rejected')->count(),
            'ongoing' => Project::where('category', 'cctv')->where('status', 'ongoing')->count(),
            'completed' => Project::where('category', 'cctv')->where('status', 'done')->count(),
        ];

        $ongoingProjects = Project::where('category', 'cctv')->where('status', 'ongoing')->with(['divisions.tasks'])->orderBy('created_at', 'desc')->get();
        $completedProjects = Project::where('category', 'cctv')->where('status', 'done')->orderBy('created_at', 'desc')->get();
        $offerProjects = Project::where('category', 'cctv')->where('status', 'offer')->orderBy('created_at', 'desc')->get();
        $progressOfferProjects = Project::where('category', 'cctv')->where('status', 'progress_offer')->get();
        $rejectedProjects = Project::where('category', 'cctv')->where('status', 'rejected')->get();

        return view('projects.cctv', compact('stats', 'ongoingProjects', 'completedProjects', 'offerProjects', 'progressOfferProjects', 'rejectedProjects'));
    }

    public function categoryDetail($category)
    {
        if (!in_array($category, ['web', 'internet', 'cctv'])) abort(404);

        $stats = [
            'total' => Project::where('category', $category)->whereNotIn('status', ['rejected'])->count(),
            'ongoing' => Project::where('category', $category)->where('status', 'ongoing')->count(),
            'done' => Project::where('category', $category)->where('status', 'done')->count(),
            'offer' => Project::where('category', $category)->whereIn('status', ['offer', 'progress_offer'])->count(),
        ];

        $projects = Project::where('category', $category)->whereNotIn('status', ['rejected'])->with('customer')->orderByDesc('created_at')->get();
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
        }, 'customer']);

        $overallProgress = $project->overall_progress;
        $slaStatus = $project->project_sla_status;
        
        $slaSummary = [
            'on_track' => $project->phases->where('sla_status', 'on_track')->count(),
            'warning' => $project->phases->where('sla_status', 'warning')->count(),
            'breached' => $project->phases->where('sla_status', 'breached')->count(),
        ];

        return view('projects.detail', compact('project', 'overallProgress', 'slaStatus', 'slaSummary'));
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
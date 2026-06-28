<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Safety check: pastikan user adalah customer
        if (!$user || $user->role?->name !== 'customer') {
            abort(403, 'Akses ditolak.');
        }

        try {
            $projects = $user->customerProjects()
                ->with(['divisions.tasks'])
                ->whereNotIn('status', ['rejected'])
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error('Customer dashboard error: ' . $e->getMessage(), [
                'user_id' => $user?->id,
                'trace' => $e->getTraceAsString()
            ]);
            $projects = collect();
        }

        // Statistik
        $totalProjects = $projects->count();
        $ongoingProjects = $projects->where('status', 'ongoing')->count();
        $completedProjects = $projects->where('status', 'done')->count();
        $categories = $projects->pluck('category')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // Share categories ke layout/sidebar (untuk menu dinamis)
        view()->share('customerCategories', $categories);

        return view('customer.dashboard', compact(
            'projects',
            'totalProjects',
            'ongoingProjects',
            'completedProjects',
            'categories'
        ));
    }

    public function show($category)
    {
        $user = Auth::user();

        if (!$user || $user->role?->name !== 'customer') {
            abort(403, 'Akses ditolak.');
        }

        $validCategories = ['web', 'internet', 'cctv'];
        if (!in_array($category, $validCategories, true)) {
            abort(404, 'Kategori tidak ditemukan.');
        }

        try {
            $projects = $user->customerProjects()
                ->where('category', $category)
                ->whereNotIn('status', ['rejected'])
                ->with(['divisions.tasks'])
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error('Customer category error: ' . $e->getMessage(), [
                'category' => $category,
                'user_id' => $user?->id
            ]);
            $projects = collect();
        }

        $totalProjects = $projects->count();
        $ongoingProjects = $projects->where('status', 'ongoing')->count();
        $completedProjects = $projects->where('status', 'done')->count();

        // Share categories ke layout/sidebar
        $allCategories = $user->customerProjects()
            ->pluck('category')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
        view()->share('customerCategories', $allCategories);

        return view('customer.category', compact(
            'projects',
            'category',
            'totalProjects',
            'ongoingProjects',
            'completedProjects'
        ));
    }

    public function showProject(Project $project)
    {
        $user = Auth::user();

        if (!$user || $user->role?->name !== 'customer') {
            abort(403, 'Akses ditolak.');
        }

        // Pastikan project milik user dan tidak rejected
        if ($project->customer_id !== $user->id || $project->status === 'rejected') {
            abort(404, 'Project tidak ditemukan');
        }

        $project->load(['divisions.tasks']);
        
        return view('customer.project-detail', compact('project'));
    }
}
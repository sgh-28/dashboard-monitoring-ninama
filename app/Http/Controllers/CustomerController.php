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
                ->with(['divisions.tasks', 'tasks'])
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
                ->with(['divisions.tasks', 'tasks'])
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

    public function projects()
    {
        return $this->index();
    }

    public function showProject(Project $project)
    {
        $user = Auth::user();

        $this->authorizeCustomerProject($project, $user);

        $project->load(['divisions.tasks', 'tasks']);

        return view('customer.project-detail', compact('project'));
    }

    public function storeFeedback(Request $request, Project $project)
    {
        $user = Auth::user();

        $this->authorizeCustomerProject($project, $user);

        $project->load('tasks');

        if (!$this->projectCanReceiveFeedback($project)) {
            return back()->with('error', 'Feedback hanya dapat dikirim setelah proyek dan seluruh task selesai.');
        }

        if ($project->customer_feedback_submitted_at) {
            return back()->with('error', 'Feedback proyek ini sudah pernah dikirim dan tidak dapat diubah kembali.');
        }

        $validated = $request->validate([
            'customer_feedback' => ['required', 'string', 'min:5', 'max:2000'],
            'customer_satisfaction_rating' => ['required', 'integer', 'between:1,5'],
        ], [
            'customer_feedback.required' => 'Keterangan feedback wajib diisi.',
            'customer_feedback.min' => 'Feedback minimal 5 karakter.',
            'customer_satisfaction_rating.required' => 'Tingkat kepuasan wajib dipilih.',
            'customer_satisfaction_rating.between' => 'Tingkat kepuasan harus dipilih antara 1 sampai 5 bintang.',
        ]);

        $project->update([
            'customer_feedback' => $validated['customer_feedback'],
            'customer_satisfaction_rating' => $validated['customer_satisfaction_rating'],
            'customer_feedback_submitted_at' => now(),
        ]);

        return redirect()
            ->route('customer.projects.show', $project)
            ->with('success', 'Terima kasih, feedback Anda berhasil disimpan.');
    }

    private function authorizeCustomerProject(Project $project, $user): void
    {
        if (!$user || $user->role?->name !== 'customer') {
            abort(403, 'Akses ditolak.');
        }

        if ($project->customer_id !== $user->id || $project->status === 'rejected') {
            abort(404, 'Project tidak ditemukan');
        }
    }

    private function projectCanReceiveFeedback(Project $project): bool
    {
        return $project->status === 'done'
            && $project->tasks->isNotEmpty()
            && $project->tasks->every(fn($task) => $task->status === 'done');
    }
}

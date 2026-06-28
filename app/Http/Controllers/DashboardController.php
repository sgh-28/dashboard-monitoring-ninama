<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Dashboard untuk User Biasa (Pegawai, Marketing, Customer)
     */
    public function index()
    {
        $categoryStats = $this->getStats();
        return view('dashboard', compact('categoryStats'));
    }

    /**
     * Dashboard Khusus Direktur (Dengan Export & Tabel Recent)
     */
    public function indexDirector(Request $request)
    {
        $categoryStats = $this->getStats();
        
        // ✅ FILTER & PENCARIAN
        $query = Project::whereNotIn('status', ['rejected'])
            ->orderByDesc('created_at');
        
        // Filter pencarian
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('client_name', 'LIKE', "%{$searchTerm}%");
            });
        }
        
        // Filter kategori
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }
        
        // Filter status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        $recentProjects = $query->limit(10)->get();
        
        // Return view khusus direktur
        return view('direktur.dashboard', compact('categoryStats', 'recentProjects'));
    }

    /**
     * Helper: Hitung Statistik Proyek
     */
    private function getStats()
    {
        return [
            'web' => [
                'total' => Project::where('category', 'web')->count(),
                'ongoing' => Project::where('category', 'web')->where('status', 'ongoing')->count(),
                'done' => Project::where('category', 'web')->where('status', 'done')->count(),
                'offer' => Project::where('category', 'web')->whereIn('status', ['offer', 'progress_offer'])->count(),
            ],
            'internet' => [
                'total' => Project::where('category', 'internet')->count(),
                'ongoing' => Project::where('category', 'internet')->where('status', 'ongoing')->count(),
                'done' => Project::where('category', 'internet')->where('status', 'done')->count(),
                'offer' => Project::where('category', 'internet')->whereIn('status', ['offer', 'progress_offer'])->count(),
            ],
            'cctv' => [
                'total' => Project::where('category', 'cctv')->count(),
                'ongoing' => Project::where('category', 'cctv')->where('status', 'ongoing')->count(),
                'done' => Project::where('category', 'cctv')->where('status', 'done')->count(),
                'offer' => Project::where('category', 'cctv')->whereIn('status', ['offer', 'progress_offer'])->count(),
            ],
        ];
    }
}
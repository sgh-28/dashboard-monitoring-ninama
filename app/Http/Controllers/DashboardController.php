<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\MarketingOffer;
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
        $googleTokenExists = file_exists(storage_path('app/google-token.json'));
        return view('dashboard', compact('categoryStats', 'googleTokenExists'));
    }

    /**
     * Dashboard Khusus Direktur (Dengan Export & Tabel Recent)
     */
    public function indexDirector(Request $request)
    {
        $categoryStats = $this->getStats();
        
        // ✅ FILTER & PENCARIAN
        $query = Project::whereIn('status', ['ongoing', 'done'])
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
        $marketingOffers = MarketingOffer::with([
                'employee',
                'project' => fn ($projectQuery) => $projectQuery->withCount(['divisions', 'tasks']),
            ])
            ->orderByDesc('offer_date')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $marketingStats = [
            'total' => MarketingOffer::count(),
            'deal' => MarketingOffer::where('status', 'deal')->count(),
            'active' => MarketingOffer::whereIn('status', ['penawaran', 'follow_up', 'meeting', 'menunggu_keputusan', 'negosiasi', 'pending'])->count(),
            'needs_account' => MarketingOffer::where('status', 'deal')->whereNull('project_id')->count(),
        ];
        
        // Return view khusus direktur
        return view('direktur.dashboard', compact('categoryStats', 'recentProjects', 'marketingOffers', 'marketingStats'));
    }

    /**
     * Helper: Hitung Statistik Proyek
     */
    private function getStats()
    {
        return [
            'web' => [
                'total' => Project::where('category', 'web')->whereIn('status', ['ongoing', 'done'])->count(),
                'ongoing' => Project::where('category', 'web')->where('status', 'ongoing')->count(),
                'done' => Project::where('category', 'web')->where('status', 'done')->count(),
            ],
            'internet' => [
                'total' => Project::where('category', 'internet')->whereIn('status', ['ongoing', 'done'])->count(),
                'ongoing' => Project::where('category', 'internet')->where('status', 'ongoing')->count(),
                'done' => Project::where('category', 'internet')->where('status', 'done')->count(),
            ],
            'cctv' => [
                'total' => Project::where('category', 'cctv')->whereIn('status', ['ongoing', 'done'])->count(),
                'ongoing' => Project::where('category', 'cctv')->where('status', 'ongoing')->count(),
                'done' => Project::where('category', 'cctv')->where('status', 'done')->count(),
            ],
        ];
    }
}

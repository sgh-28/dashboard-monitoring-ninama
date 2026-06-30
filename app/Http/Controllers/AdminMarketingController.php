<?php

namespace App\Http\Controllers;

use App\Models\MarketingOffer;
use App\Exports\MarketingOffersExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class AdminMarketingController extends Controller
{
    /**
     * Dashboard Laporan Marketing untuk Admin/Direktur
     */
    public function index(Request $request)
    {
        $query = MarketingOffer::with([
            'employee',
            'project' => fn ($projectQuery) => $projectQuery->withCount(['divisions', 'tasks']),
        ]);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('company_address', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('contact_phone', 'like', "%{$search}%");
            });
        }

        $offers = $query->orderByDesc('offer_date')
            ->orderByDesc('created_at')
            ->paginate(15);

        $stats = [
            'total' => MarketingOffer::count(),
            'deal' => MarketingOffer::where('status', 'deal')->count(),
            'active' => MarketingOffer::whereIn('status', ['penawaran', 'follow_up', 'meeting', 'menunggu_keputusan', 'negosiasi', 'pending'])->count(),
            'rejected' => MarketingOffer::whereIn('status', ['rejected', 'no_response'])->count(),
            'needs_account' => MarketingOffer::where('status', 'deal')
                ->whereNull('project_id')
                ->get()
                ->filter(fn($offer) => $offer->needsCustomerAccount())
                ->count(),
        ];

        return view('admin.marketing.index', compact('offers', 'stats'));
    }

    /**
     * Detail Penawaran (Modal/Popup)
     */
    public function show(MarketingOffer $offer)
    {
        return view('admin.marketing.show', compact('offer'));
    }

    /**
     * ✅ BARU: Export Marketing Offers to Excel
     */
    public function exportMarketing($category = null)
    {
        return Excel::download(
            new MarketingOffersExport($category),
            'laporan-marketing-' . ($category ?? 'semua') . '-' . date('Y-m-d') . '.xlsx',
            null,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }
}

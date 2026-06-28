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
        $query = MarketingOffer::with(['employee', 'project']);

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
                  ->orWhere('company_address', 'like', "%{$search}%");
            });
        }

        $offers = $query->orderByDesc('offer_date')->paginate(15);

        $stats = [
            'total' => MarketingOffer::count(),
            'deal' => MarketingOffer::where('status', 'deal')->count(),
            'pending' => MarketingOffer::whereIn('status', ['pending', 'menunggu_keputusan'])->count(),
            'rejected' => MarketingOffer::where('status', 'rejected')->count(),
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
            'laporan-marketing-' . ($category ?? 'semua') . '-' . date('Y-m-d') . '.xlsx'
        );
    }
}
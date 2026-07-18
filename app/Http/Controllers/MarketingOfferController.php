<?php

namespace App\Http\Controllers;

use App\Models\MarketingOffer;
use App\Models\MarketingOfferHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketingOfferController extends Controller
{
    /**
     * Tampilkan daftar penawaran milik marketing yang sedang login
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
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('company_address', 'like', "%{$search}%");
            });
        }

        $statsBase = MarketingOffer::query();
        $stats = [
            'total' => (clone $statsBase)->count(),
            'active' => (clone $statsBase)->whereIn('status', ['penawaran', 'follow_up', 'meeting', 'menunggu_keputusan', 'negosiasi', 'pending'])->count(),
            'deal' => (clone $statsBase)->where('status', 'deal')->count(),
            'needs_account' => (clone $statsBase)
                ->where('status', 'deal')
                ->whereNull('project_id')
                ->get()
                ->filter(fn($offer) => $offer->needsCustomerAccount())
                ->count(),
            'rejected' => (clone $statsBase)->whereIn('status', ['rejected', 'no_response'])->count(),
        ];

        $offers = $query->orderByDesc('offer_date')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('marketing.index', compact('offers', 'stats'));
    }

    /**
     * Tampilkan form tambah penawaran
     */
    public function create()
    {
        return view('marketing.create');
    }

    /**
     * Simpan penawaran baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'required|string',
            'contact_person' => 'required|string|max:255',
            'contact_position' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'category' => 'required|in:web,internet,cctv',
            'offer_description' => 'nullable|string',
            'estimated_value' => 'nullable|numeric|min:0',
            'offer_date' => 'required|date',
        ]);

        // Tambahkan ID user yang sedang login
        $validated['employee_id'] = Auth::id();
        $validated['status'] = 'penawaran';

        MarketingOffer::create($validated);

        return redirect()->route('marketing.index')->with('success', 'Data penawaran berhasil disimpan!');
    }

    /**
     * Tampilkan form edit
     */
    public function edit(MarketingOffer $offer)
    {
        $offer->load(['histories.changedBy']);

        return view('marketing.edit', compact('offer'));
    }

    /**
     * Update penawaran
     */
    public function update(Request $request, MarketingOffer $offer)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'required|string',
            'contact_person' => 'required|string|max:255',
            'contact_position' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'category' => 'required|in:web,internet,cctv',
            'offer_description' => 'nullable|string',
            'estimated_value' => 'nullable|numeric|min:0',
            'offer_date' => 'required|date',
            'has_status_update' => 'nullable|boolean',
            'follow_up_date' => 'nullable|date',
            'meeting_date' => 'nullable|date',
            'status' => 'nullable|in:penawaran,follow_up,meeting,menunggu_keputusan,negosiasi,deal,pending,rejected,no_response',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $hasStatusUpdate = $request->boolean('has_status_update') && $request->filled('status');

        if (!$hasStatusUpdate) {
            unset(
                $validated['has_status_update'],
                $validated['status'],
                $validated['follow_up_date'],
                $validated['meeting_date'],
                $validated['reason'],
                $validated['notes']
            );

            $offer->update($validated);

            return redirect()->route('marketing.index')->with('success', 'Data penawaran berhasil diupdate!');
        }

        unset($validated['has_status_update']);

        $shouldSaveHistory = $offer->status !== $validated['status']
            || ($offer->follow_up_date?->format('Y-m-d') ?? '') !== ($validated['follow_up_date'] ?? '')
            || ($offer->meeting_date?->format('Y-m-d\TH:i') ?? '') !== ($validated['meeting_date'] ?? '')
            || (string) ($offer->reason ?? '') !== (string) ($validated['reason'] ?? '')
            || (string) ($offer->notes ?? '') !== (string) ($validated['notes'] ?? '');

        $offer->update($validated);

        if ($shouldSaveHistory) {
            MarketingOfferHistory::create([
                'marketing_offer_id' => $offer->id,
                'changed_by' => Auth::id(),
                'status' => $validated['status'],
                'follow_up_date' => $validated['follow_up_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);
        }

        return redirect()->route('marketing.index')->with('success', 'Data penawaran berhasil diupdate!');
    }

    /**
     * Hapus penawaran
     */
    public function destroy(MarketingOffer $offer)
    {
        $offer->delete();
        return back()->with('success', 'Data penawaran dihapus.');
    }

}

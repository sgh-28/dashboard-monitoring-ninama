<?php

namespace App\Http\Controllers;

use App\Models\MarketingOffer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketingOfferController extends Controller
{
    /**
     * Tampilkan daftar penawaran milik marketing yang sedang login
     */
    public function index()
    {
        $offers = MarketingOffer::where('employee_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return view('marketing.index', compact('offers'));
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
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'category' => 'required|in:web,internet,cctv',
            'offer_description' => 'nullable|string',
            'estimated_value' => 'nullable|numeric|min:0',
            'offer_date' => 'required|date',
            'follow_up_date' => 'nullable|date',
            'meeting_date' => 'nullable|date',
            'status' => 'required|in:penawaran,follow_up,meeting,menunggu_keputusan,negosiasi,deal,pending,rejected,no_response',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Tambahkan ID user yang sedang login
        $validated['employee_id'] = Auth::id();

        MarketingOffer::create($validated);

        return redirect()->route('marketing.index')->with('success', 'Data penawaran berhasil disimpan!');
    }

    /**
     * Tampilkan form edit
     */
    public function edit(MarketingOffer $offer)
    {
        // Cek kepemilikan
        if ($offer->employee_id !== Auth::id()) {
            abort(403);
        }
        return view('marketing.edit', compact('offer'));
    }

    /**
     * Update penawaran
     */
    public function update(Request $request, MarketingOffer $offer)
    {
        if ($offer->employee_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'required|string',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'category' => 'required|in:web,internet,cctv',
            'offer_description' => 'nullable|string',
            'estimated_value' => 'nullable|numeric|min:0',
            'offer_date' => 'required|date',
            'follow_up_date' => 'nullable|date',
            'meeting_date' => 'nullable|date',
            'status' => 'required|in:penawaran,follow_up,meeting,menunggu_keputusan,negosiasi,deal,pending,rejected,no_response',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $offer->update($validated);

        return redirect()->route('marketing.index')->with('success', 'Data penawaran berhasil diupdate!');
    }

    /**
     * Hapus penawaran
     */
    public function destroy(MarketingOffer $offer)
    {
        if ($offer->employee_id !== Auth::id()) {
            abort(403);
        }

        $offer->delete();
        return back()->with('success', 'Data penawaran dihapus.');
    }
}
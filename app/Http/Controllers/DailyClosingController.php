<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDailyClosingRequest;
use App\Models\DailyClosing;
use App\Services\DailyClosingService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class DailyClosingController extends Controller
{
    public function __construct(
        private readonly DailyClosingService $service
    ) {}

    /**
     * Close the book for a given date.
     */
    public function store(StoreDailyClosingRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        
        // Normalize cash_physical and atm_physical from string representation
        $cashPhysical = (float) str_replace(['.', ','], ['', '.'], $validated['cash_physical']);
        $atmPhysical  = (float) str_replace(['.', ','], ['', '.'], $validated['atm_physical']);
        
        $this->service->closeBook(
            $validated['closing_date'],
            $cashPhysical,
            $atmPhysical,
            $validated['notes'] ?? null,
            $request->user()
        );

        return back()->with('success', 'Buku tanggal ' . Carbon::parse($validated['closing_date'])->format('d/m/Y') . ' berhasil ditutup.');
    }

    /**
     * Verify the closed daily report (Super Admin only).
     */
    public function verify(Request $request, DailyClosing $dailyClosing): RedirectResponse
    {
        if (!$request->user()->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang dapat melakukan verifikasi.');
        }

        try {
            $this->service->verifyClosing($dailyClosing, $request->user());
            return back()->with('success', 'Laporan keuangan harian telah diverifikasi.');
        } catch (\LogicException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Revert the closed/verified daily report to draft (Super Admin only).
     */
    public function revert(Request $request, DailyClosing $dailyClosing): RedirectResponse
    {
        if (!$request->user()->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang dapat membuka kunci laporan.');
        }

        $this->service->revertToDraft($dailyClosing);
        return back()->with('success', 'Laporan keuangan harian berhasil dikembalikan ke draft (unlocked).');
    }

    /**
     * Get closing metrics for a given date as JSON.
     */
    public function getClosingData(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date'],
        ]);

        $data = $this->service->getClosingDataForDate($request->date);
        
        // Also check if already closed/verified
        $closing = DailyClosing::whereDate('closing_date', $request->date)->first();
        $data['status'] = $closing ? $closing->status : 'draft';
        $data['notes'] = $closing ? $closing->notes : '';
        $data['cash_physical'] = $closing ? (float)$closing->cash_physical : 0.0;
        $data['atm_physical'] = $closing ? (float)$closing->atm_physical : 0.0;

        return response()->json($data);
    }
}

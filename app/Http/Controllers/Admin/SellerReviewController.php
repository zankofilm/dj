<?php

namespace App\Http\Controllers\Admin;

use App\Models\AuditLog;
use App\Models\Seller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SellerReviewController
{
    public function index(): View
    {
        abort_unless(request()->user()?->hasPermission('sellers.review'), 403);

        $sellers = Seller::with('owner')->latest()->paginate(25);

        return view('admin.sellers.index', compact('sellers'));
    }

    public function decide(Request $request, Seller $seller): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('sellers.review'), 403);

        $data = $request->validate([
            'decision' => ['required', Rule::in(['approve', 'reject', 'changes_requested'])],
            'reason' => ['nullable', 'string', 'max:3000'],
        ]);

        if ($seller->status !== 'submitted' && $seller->status !== 'under_review') {
            return back()->withErrors(['decision' => __('seller.not_reviewable')]);
        }

        if ($data['decision'] !== 'approve' && blank($data['reason'])) {
            return back()->withErrors(['reason' => __('seller.reason_required')]);
        }

        DB::transaction(function () use ($request, $seller, $data) {
            $before = ['status' => $seller->status];

            $seller->status = match ($data['decision']) {
                'approve' => 'approved',
                'reject' => 'rejected',
                default => 'changes_requested',
            };
            $seller->reviewed_by = $request->user()->id;
            $seller->reviewed_at = now();
            $seller->rejection_reason = $data['reason'] ?? null;
            $seller->save();

            AuditLog::create([
                'actor_user_id' => $request->user()->id,
                'action' => 'seller.application.reviewed',
                'subject_type' => Seller::class,
                'subject_id' => (string) $seller->id,
                'before_data' => $before,
                'after_data' => ['status' => $seller->status, 'reason' => $seller->rejection_reason],
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 2000),
                'created_at' => now(),
            ]);
        });

        return back()->with('status', __('seller.review_saved'));
    }
}

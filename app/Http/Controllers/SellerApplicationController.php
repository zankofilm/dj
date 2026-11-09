<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSellerApplicationRequest;
use App\Models\AuditLog;
use App\Models\Seller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SellerApplicationController
{
    public function create(): View
    {
        return view('seller.apply');
    }

    public function store(StoreSellerApplicationRequest $request): RedirectResponse
    {
        $seller = DB::transaction(function () use ($request) {
            $existing = Seller::where('owner_user_id', $request->user()->id)
                ->whereIn('status', ['draft', 'submitted', 'under_review', 'approved'])
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            return Seller::create([
                'public_id' => (string) Str::uuid(),
                'owner_user_id' => $request->user()->id,
                ...$request->validated(),
                'status' => 'submitted',
            ]);

            AuditLog::create([
                'actor_user_id' => $request->user()->id,
                'action' => 'seller.application.submitted',
                'subject_type' => Seller::class,
                'subject_id' => (string) $seller->id,
                'after_data' => ['status' => 'submitted', 'display_name' => $seller->display_name],
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 2000),
                'created_at' => now(),
            ]);

        });

        $isExisting = $seller->wasRecentlyCreated === false;
        return redirect()->route('seller.application.show', $seller)
            ->with('status', $isExisting ? __('seller.application_exists') : __('seller.application_submitted'));
    }

    public function show(Seller $seller): View
    {
        abort_unless($seller->owner_user_id === request()->user()->id
            || request()->user()->hasPermission('sellers.review'), 403);

        return view('seller.status', compact('seller'));
    }
}

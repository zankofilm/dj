<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CatalogManagementController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->hasPermission('catalog.manage'), 403);

        $products = Product::with(['seller', 'category'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = trim($request->string('q')->toString());
                $q->where('slug', 'like', "%{$term}%");
            })
            ->latest()->paginate(25)->withQueryString();

        return view('admin.catalog.index', compact('products'));
    }

    public function updateStatus(Request $request, Product $product)
    {
        abort_unless($request->user()?->hasPermission('catalog.manage'), 403);

        $data = $request->validate([
            'status' => ['required', Rule::in(['draft', 'active', 'published', 'rejected', 'suspended'])],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $before = $product->status;
        DB::transaction(function () use ($request, $product, $data, $before) {
            $product->update([
                'status' => $data['status'],
                'published_at' => in_array($data['status'], ['active', 'published'], true)
                    ? ($product->published_at ?? now()) : $product->published_at,
            ]);

            \App\Models\AuditLog::create([
                'actor_user_id' => $request->user()->id,
                'action' => 'catalog.product.status_updated',
                'subject_type' => Product::class,
                'subject_id' => (string) $product->id,
                'before_data' => ['status' => $before],
                'after_data' => ['status' => $data['status'], 'reason' => $data['reason'] ?? null],
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 2000),
                'created_at' => now(),
            ]);
        });

        return back()->with('status', 'وضعیت محصول به‌روزرسانی شد.');
    }
}

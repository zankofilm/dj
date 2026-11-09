<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $seller = Seller::where('owner_user_id', request()->user()->id)
            ->where('status', 'approved')->firstOrFail();

        $products = Product::with('media')->where('seller_id', $seller->id)->latest()->paginate(20);

        return view('seller.products.index', compact('seller', 'products'));
    }

    public function create(): View
    {
        $seller = Seller::where('owner_user_id', request()->user()->id)
            ->where('status', 'approved')->firstOrFail();

        $categories = \App\Models\Category::where('status', 'active')->orderBy('sort_order')->get();

        return view('seller.products.create', compact('seller', 'categories'));
    }

    public function updateMediaAlt(\Illuminate\Http\Request $request, \App\Models\Product $product, \App\Models\ProductMedia $media): RedirectResponse
    {
        $seller = Seller::where('owner_user_id', $request->user()->id)
            ->where('status', 'approved')->firstOrFail();

        abort_unless((int) $product->seller_id === (int) $seller->id, 404);
        abort_unless((int) $media->product_id === (int) $product->id, 404);

        $data = $request->validate([
            'alt_translations' => ['required', 'array'],
            'alt_translations.fa' => ['nullable', 'string', 'max:250'],
            'alt_translations.ku' => ['nullable', 'string', 'max:250'],
            'alt_translations.en' => ['nullable', 'string', 'max:250'],
        ]);

        $media->update([
            'alt_translations' => [
                'fa' => trim((string) ($data['alt_translations']['fa'] ?? '')),
                'ku' => trim((string) ($data['alt_translations']['ku'] ?? '')),
                'en' => trim((string) ($data['alt_translations']['en'] ?? '')),
            ],
        ]);

        AuditLog::create([
            'actor_user_id' => $request->user()->id,
            'action' => 'catalog.product_media.alt_updated',
            'subject_type' => \App\Models\ProductMedia::class,
            'subject_id' => (string) $media->id,
            'after_data' => ['alt_translations' => $media->alt_translations],
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 2000),
            'created_at' => now(),
        ]);

        return back()->with('status', 'متن جایگزین تصاویر به‌روزرسانی شد.');
    }

    public function reorderMedia(\Illuminate\Http\Request $request, \App\Models\Product $product): RedirectResponse
    {
        $seller = Seller::where('owner_user_id', $request->user()->id)
            ->where('status', 'approved')->firstOrFail();

        abort_unless((int) $product->seller_id === (int) $seller->id, 404);

        $data = $request->validate([
            'media_ids' => ['required', 'array', 'min:1', 'max:5'],
            'media_ids.*' => ['required', 'integer', 'distinct'],
        ]);

        DB::transaction(function () use ($request, $product, $data, $seller) {
            $lockedProduct = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();
            abort_unless((int) $lockedProduct->seller_id === (int) $seller->id, 404);
            $currentIds = $lockedProduct->media()->lockForUpdate()->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
            $submittedIds = collect($data['media_ids'])->map(fn ($id) => (int) $id)->sort()->values()->all();

            abort_unless($currentIds === $submittedIds, 422, 'فهرست تصاویر تغییر کرده است؛ صفحه را تازه‌سازی کنید.');

            foreach ($data['media_ids'] as $position => $mediaId) {
                $lockedProduct->media()->whereKey($mediaId)->update(['sort_order' => $position]);
            }

            AuditLog::create([
                'actor_user_id' => $request->user()->id,
                'action' => 'catalog.product_media.reordered',
                'subject_type' => Product::class,
                'subject_id' => (string) $product->id,
                'after_data' => ['media_ids' => array_map('intval', $data['media_ids'])],
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 2000),
                'created_at' => now(),
            ]);
        });

        return back()->with('status', 'ترتیب تصاویر ذخیره شد.');
    }

    public function setPrimaryMedia(\Illuminate\Http\Request $request, \App\Models\Product $product, \App\Models\ProductMedia $media): RedirectResponse
    {
        $seller = Seller::where('owner_user_id', $request->user()->id)
            ->where('status', 'approved')->firstOrFail();

        abort_unless((int) $product->seller_id === (int) $seller->id, 404);
        abort_unless((int) $media->product_id === (int) $product->id, 404);

        DB::transaction(function () use ($request, $product, $media) {
            $product->media()->lockForUpdate()->update(['is_primary' => false]);
            $media->update(['is_primary' => true]);

            AuditLog::create([
                'actor_user_id' => $request->user()->id,
                'action' => 'catalog.product_media.primary_changed',
                'subject_type' => \App\Models\ProductMedia::class,
                'subject_id' => (string) $media->id,
                'after_data' => ['product_id' => $product->id, 'is_primary' => true],
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 2000),
                'created_at' => now(),
            ]);
        });

        return back()->with('status', 'تصویر اصلی محصول تغییر کرد.');
    }

    public function destroyMedia(\Illuminate\Http\Request $request, \App\Models\Product $product, \App\Models\ProductMedia $media): RedirectResponse
    {
        $seller = Seller::where('owner_user_id', $request->user()->id)
            ->where('status', 'approved')->firstOrFail();

        abort_unless((int) $product->seller_id === (int) $seller->id, 404);
        abort_unless((int) $media->product_id === (int) $product->id, 404);

        $path = $media->storage_path;
        $wasPrimary = (bool) $media->is_primary;

        DB::transaction(function () use ($request, $product, $media, $wasPrimary) {
            $media->delete();

            if ($wasPrimary) {
                $next = $product->media()->orderBy('sort_order')->orderBy('id')->first();
                if ($next) {
                    $product->media()->update(['is_primary' => false]);
                    $next->update(['is_primary' => true]);
                }
            }

            AuditLog::create([
                'actor_user_id' => $request->user()->id,
                'action' => 'catalog.product_media.deleted',
                'subject_type' => \App\Models\ProductMedia::class,
                'subject_id' => (string) $media->id,
                'before_data' => ['product_id' => $product->id, 'storage_path' => $path],
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 2000),
                'created_at' => now(),
            ]);
        });

        Storage::disk('public')->delete($path);

        return back()->with('status', 'تصویر محصول حذف شد.');
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $seller = Seller::where('owner_user_id', $request->user()->id)
            ->where('status', 'approved')->firstOrFail();

        abort_unless((int) $request->validated('seller_id') === $seller->id, 403);

        $storedPaths = [];
        try {
            $product = DB::transaction(function () use ($request, $seller, &$storedPaths) {
            $product = Product::create([
                'public_id' => (string) Str::uuid(),
                'seller_id' => $seller->id,
                'category_id' => $request->validated('category_id'),
                'slug' => $request->validated('slug'),
                'status' => 'pending_review',
                'currency' => strtoupper($request->validated('currency')),
                'base_price_minor' => $request->validated('base_price_minor'),
                'translations' => $request->validated('translations'),
                'attributes' => $request->validated('attributes') ?? [],
                'product_type' => $request->validated('product_type'),
                'condition' => $request->validated('condition'),
                'brand_name' => $request->validated('brand_name'),
            ]);

            foreach ($request->file('images', []) as $index => $image) {
                $path = $image->store('products/'.$seller->id, 'public');
                if (!$path) {
                    throw new \RuntimeException('ذخیره یکی از تصاویر محصول ناموفق بود.');
                }
                $storedPaths[] = $path;

                $product->media()->create([
                    'storage_path' => $path,
                    'mime_type' => (string) $image->getMimeType(),
                    'file_size' => (int) $image->getSize(),
                    'sort_order' => $index,
                    'is_primary' => $index === 0,
                    'alt_translations' => [
                        'fa' => $product->translations['fa']['title'] ?? '',
                        'ku' => $product->translations['ku']['title'] ?? '',
                        'en' => $product->translations['en']['title'] ?? '',
                    ],
                ]);
            }

            AuditLog::create([
                'actor_user_id' => $request->user()->id,
                'action' => 'catalog.product.created',
                'subject_type' => Product::class,
                'subject_id' => (string) $product->id,
                'after_data' => ['status' => 'pending_review', 'seller_id' => $seller->id],
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 2000),
                'created_at' => now(),
            ]);

            return $product;
            });
        } catch (\Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk('public')->delete($path);
            }
            throw $exception;
        }

        return redirect()->route('seller.products.index')
            ->with('status', 'محصول ثبت شد و پس از بررسی منتشر خواهد شد.');
    }
}

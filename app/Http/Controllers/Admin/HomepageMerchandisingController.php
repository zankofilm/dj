<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomepageMerchandisingController extends Controller
{
    private function authorizeManager(Request $request): void
    {
        abort_unless($request->user()?->hasPermission('homepage.manage'), 403);
    }

    public function index(Request $request): View
    {
        $this->authorizeManager($request);

        $categories = Category::query()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->get();

        $selectedProductIds = Product::query()
            ->where('is_homepage_featured', true)
            ->orderBy('homepage_sort_order')
            ->pluck('id')
            ->all();

        $products = Product::query()
            ->with(['seller', 'category'])
            ->whereIn('status', ['active', 'published'])
            ->whereNotNull('published_at')
            ->whereHas('seller', fn ($query) => $query->where('status', 'approved'))
            ->latest('published_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.homepage-merchandising.index', compact('categories', 'products', 'selectedProductIds'));
    }

    public function update(Request $request)
    {
        $this->authorizeManager($request);

        $data = $request->validate([
            'categories' => ['nullable', 'array', 'max:12'],
            'categories.*' => ['integer', 'distinct', 'exists:categories,id'],
            'products' => ['nullable', 'array', 'max:12'],
            'products.*' => ['integer', 'distinct', 'exists:products,id'],
        ], [
            'categories.max' => 'حداکثر ۱۲ دسته‌بندی ویژه انتخاب کنید.',
            'products.max' => 'حداکثر ۱۲ محصول ویژه انتخاب کنید.',
        ]);

        $categoryIds = array_values(array_map('intval', $data['categories'] ?? []));
        $productIds = array_values(array_map('intval', $data['products'] ?? []));

        $validCategoryCount = Category::query()
            ->whereIn('id', $categoryIds)->where('status', 'active')->count();
        $validProductCount = Product::query()
            ->whereIn('id', $productIds)
            ->whereIn('status', ['active', 'published'])
            ->whereNotNull('published_at')
            ->whereHas('seller', fn ($query) => $query->where('status', 'approved'))
            ->count();

        abort_unless($validCategoryCount === count($categoryIds), 422, 'یکی از دسته‌بندی‌های انتخاب‌شده فعال نیست.');
        abort_unless($validProductCount === count($productIds), 422, 'یکی از محصولات انتخاب‌شده قابل نمایش عمومی نیست.');

        DB::transaction(function () use ($request, $categoryIds, $productIds) {
            $before = [
                'categories' => Category::where('is_homepage_featured', true)->orderBy('homepage_sort_order')->pluck('id')->all(),
                'products' => Product::where('is_homepage_featured', true)->orderBy('homepage_sort_order')->pluck('id')->all(),
            ];

            Category::query()->update(['is_homepage_featured' => false, 'homepage_sort_order' => 0]);
            Product::query()->update(['is_homepage_featured' => false, 'homepage_sort_order' => 0]);

            foreach ($categoryIds as $index => $id) {
                Category::whereKey($id)->update([
                    'is_homepage_featured' => true,
                    'homepage_sort_order' => $index + 1,
                ]);
            }
            foreach ($productIds as $index => $id) {
                Product::whereKey($id)->update([
                    'is_homepage_featured' => true,
                    'homepage_sort_order' => $index + 1,
                ]);
            }

            AuditLog::create([
                'actor_user_id' => $request->user()->id,
                'action' => 'homepage.merchandising.updated',
                'subject_type' => 'homepage_merchandising',
                'subject_id' => 'featured-selection',
                'before_data' => $before,
                'after_data' => ['categories' => $categoryIds, 'products' => $productIds],
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 2000),
                'created_at' => now(),
            ]);
        });

        return redirect()->route('admin.homepage-merchandising.index')
            ->with('status', 'دسته‌بندی‌ها و محصولات منتخب هوم‌پیج ذخیره شدند.');
    }
}

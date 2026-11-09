<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\HomepageBanner;
use App\Models\HomepageShelfSetting;
use App\Models\Product;
use App\Models\Seller;
use App\Models\SiteAppearanceSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class StorefrontController extends Controller
{
    private function publicProducts(): Builder
    {
        return Product::query()
            ->with(['media', 'seller', 'category', 'variants' => fn ($variants) => $variants->where('status', 'active')])
            ->whereIn('status', ['active', 'published'])
            ->whereNotNull('published_at')
            ->whereHas('seller', fn ($seller) => $seller->where('status', 'approved'));
    }

    private function applyBrowseFilters(Builder $query, Request $request): Builder
    {
        $locale = app()->getLocale();
        $term = trim((string) $request->query('q', ''));

        if ($term !== '') {
            $escaped = addcslashes($term, '\\%_');
            $query->where(function (Builder $nested) use ($escaped, $term) {
                $nested->where('slug', 'like', "%{$escaped}%")
                    ->orWhere('brand_name', 'like', "%{$escaped}%")
                    ->orWhere('translations', 'like', "%{$escaped}%");
            });
        }

        if ($request->filled('seller')) {
            $query->where('seller_id', (int) $request->query('seller'));
        }

        if ($request->filled('min_price')) {
            $query->where('base_price_minor', '>=', (int) $request->query('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('base_price_minor', '<=', (int) $request->query('max_price'));
        }

        $sort = (string) $request->query('sort', 'newest');
        match ($sort) {
            'price_asc' => $query->orderBy('base_price_minor')->orderByDesc('published_at'),
            'price_desc' => $query->orderByDesc('base_price_minor')->orderByDesc('published_at'),
            default => $query->orderByDesc('published_at'),
        };

        return $query;
    }

    private function browseData(): array
    {
        return [
            'sellers' => Seller::query()
                ->where('status', 'approved')
                ->whereHas('products', fn ($products) => $products
                    ->whereIn('status', ['active', 'published'])
                    ->whereNotNull('published_at'))
                ->orderBy('display_name')
                ->get(['id', 'display_name']),
            'categories' => Category::query()
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->limit(40)
                ->get(),
        ];
    }

    public function index(Request $request): View
    {
        $locale = app()->getLocale();
        $siteAppearance = SiteAppearanceSetting::allValues();
        $shelfSettings = HomepageShelfSetting::query()->get()->keyBy('shelf_key');
        $shelfLimit = fn (string $key): int => (int) ($shelfSettings->get($key)?->item_limit ?? 12);

        $categoryQuery = Category::query()
            ->where('status', 'active')
            ->withCount(['products' => fn ($query) => $query
                ->whereIn('status', ['active', 'published'])
                ->whereNotNull('published_at')
                ->whereHas('seller', fn ($seller) => $seller->where('status', 'approved'))]);

        $featuredCategories = (clone $categoryQuery)
            ->where('is_homepage_featured', true)
            ->orderBy('homepage_sort_order')
            ->limit(12)
            ->get();

        $categories = $featuredCategories->isNotEmpty()
            ? $featuredCategories
            : (clone $categoryQuery)->orderBy('sort_order')->limit(12)->get();

        $featuredProducts = $this->publicProducts()
            ->where('is_homepage_featured', true)
            ->orderBy('homepage_sort_order')
            ->limit(12)
            ->get();

        $products = $this->applyBrowseFilters($this->publicProducts(), $request)
            ->paginate(24)
            ->withQueryString();

        $homepageBanners = HomepageBanner::query()
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->orderBy('sort_order')->orderBy('id')->limit(8)->get();

        $latestProducts = $this->publicProducts()
            ->latest('published_at')->limit($shelfLimit('latest'))->get();

        $cheapestProducts = $this->publicProducts()
            ->orderBy('base_price_minor')->limit($shelfLimit('cheapest'))->get();

        // Popularity is derived only from successfully paid orders; no fabricated ranking.
        $popularIds = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'paid')
            ->whereNotNull('order_items.product_id')
            ->select('order_items.product_id', DB::raw('SUM(order_items.quantity) as units_sold'))
            ->groupBy('order_items.product_id')
            ->orderByDesc('units_sold')
            ->limit($shelfLimit('popular'))
            ->pluck('order_items.product_id');

        $popularProducts = $this->publicProducts()
            ->whereIn('id', $popularIds)
            ->get()
            ->sortBy(fn ($product) => $popularIds->search($product->id))
            ->values();

        // A sale is shown only when an active variant has a compare-at price above its effective price.
        $discountedProducts = $this->publicProducts()
            ->whereHas('variants', function ($variants) {
                $variants->where('status', 'active')
                    ->whereNotNull('compare_at_price_minor')
                    ->whereRaw('compare_at_price_minor > COALESCE(price_minor, (SELECT base_price_minor FROM products WHERE products.id = product_variants.product_id))');
            })
            ->with(['variants' => fn ($variants) => $variants->where('status', 'active')
                ->whereNotNull('compare_at_price_minor')
                ->whereRaw('compare_at_price_minor > COALESCE(price_minor, (SELECT base_price_minor FROM products WHERE products.id = product_variants.product_id))')])
            ->limit($shelfLimit('discount'))->get();

        return view('storefront', array_merge(
            compact('categories', 'featuredProducts', 'products', 'locale', 'homepageBanners', 'siteAppearance', 'shelfSettings', 'latestProducts', 'popularProducts', 'cheapestProducts', 'discountedProducts'),
            $this->browseData()
        ));
    }

    public function category(Request $request, string $slug): View
    {
        $category = Category::query()
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $products = $this->applyBrowseFilters(
            $this->publicProducts()->where('category_id', $category->id),
            $request
        )->paginate(24)->withQueryString();

        return view('categories.show', array_merge(
            compact('category', 'products'),
            $this->browseData()
        ));
    }

    public function show(string $publicId): View
    {
        $product = $this->publicProducts()
            ->with(['variants' => fn ($query) => $query->where('status', 'active')])
            ->where('public_id', $publicId)
            ->firstOrFail();

        $relatedProducts = $this->publicProducts()
            ->where('category_id', $product->category_id)
            ->where('id', '<>', $product->id)
            ->latest('published_at')
            ->limit(8)
            ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }
}

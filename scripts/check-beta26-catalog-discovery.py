#!/usr/bin/env python3
from pathlib import Path
import sys
base=Path(__file__).resolve().parents[1]
controller=(base/"app/Http/Controllers/StorefrontController.php").read_text(encoding="utf-8")
routes=(base/"routes/web.php").read_text(encoding="utf-8")
home=(base/"resources/views/storefront.blade.php").read_text(encoding="utf-8")
category=(base/"resources/views/categories/show.blade.php").read_text(encoding="utf-8")
css=(base/"resources/css/app.css").read_text(encoding="utf-8")
checks=[
("public product base query excludes unpublished products","whereNotNull('published_at')" in controller),
("public product base query requires approved seller","whereHas('seller'" in controller),
("search includes product slug","->where('slug', 'like'" in controller),
("search includes brand","->orWhere('brand_name', 'like'" in controller),
("search checks stored translations","->orWhere('translations', 'like'" in controller),
("seller filter is supported","where('seller_id', (int) $request->query('seller'))" in controller),
("minimum price filter is supported","where('base_price_minor', '>=', (int) $request->query('min_price'))" in controller),
("maximum price filter is supported","where('base_price_minor', '<=', (int) $request->query('max_price'))" in controller),
("cheap-first sorting is supported","'price_asc' => $query->orderBy('base_price_minor')" in controller),
("expensive-first sorting is supported","'price_desc' => $query->orderByDesc('base_price_minor')" in controller),
("category route exists","Route::get('/categories/{slug}'" in routes),
("category page filters by category ID","where('category_id', $category->id)" in controller),
("category chips link to real category pages","route('categories.show', $category->slug)" in home),
("homepage has GET search/filter form","method=\"GET\" action=\"{{ route('storefront.home') }}\"" in home),
("category archive has GET filter form","method=\"GET\" action=\"{{ route('categories.show', $category->slug) }}\"" in category),
("pagination preserves query filters","withQueryString()" in controller),
("filter panel has responsive layout","@media (max-width:640px)" in css and ".catalog-filters" in css),
("category SEO canonical exists",'rel=\"canonical\"' in category),
]
failed=[]
for label,ok in checks:
 print(("PASS: " if ok else "FAIL: ")+label)
 if not ok: failed.append(label)
if failed:
 print("FAILED:",failed); sys.exit(1)
print(f"PASS: {len(checks)} catalog discovery checks.")

#!/usr/bin/env python3
from pathlib import Path
import sys
base=Path(__file__).resolve().parents[1]
migration=(base/"database/migrations/2026_01_01_000021_add_homepage_featured_flags.php").read_text(encoding="utf-8")
controller=(base/"app/Http/Controllers/Admin/HomepageMerchandisingController.php").read_text(encoding="utf-8")
store=(base/"app/Http/Controllers/StorefrontController.php").read_text(encoding="utf-8")
routes=(base/"routes/web.php").read_text(encoding="utf-8")
layout=(base/"resources/views/layouts/admin.blade.php").read_text(encoding="utf-8")
admin=(base/"resources/views/admin/homepage-merchandising/index.blade.php").read_text(encoding="utf-8")
home=(base/"resources/views/storefront.blade.php").read_text(encoding="utf-8")
revision=(base/"app/Http/Controllers/Admin/HomepageDesignRevisionController.php").read_text(encoding="utf-8")
provider=(base/"app/Providers/MarketplaceServiceProvider.php").read_text(encoding="utf-8")
header=(base/"resources/views/partials/storefront-header.blade.php").read_text(encoding="utf-8")
footer=(base/"resources/views/partials/storefront-footer.blade.php").read_text(encoding="utf-8")
def name_update(text):
 return "name('homepage-merchandising.update')" in text
checks=[
("migration adds featured flags and stable order to categories","Schema::table('categories'" in migration and "is_homepage_featured" in migration and "homepage_sort_order" in migration),
("migration adds featured flags and stable order to products","Schema::table('products'" in migration and migration.count("is_homepage_featured") >= 2),
("merchandising controller enforces homepage permission","hasPermission('homepage.manage')" in controller),
("merchandising validates max 12 and distinct IDs","'max:12'" in controller and "'distinct'" in controller),
("merchandising rejects inactive or unpublished entities","where('status', 'active')" in controller and "whereNotNull('published_at')" in controller and "whereHas('seller'" in controller),
("merchandising update is transactional and audited","DB::transaction" in controller and "homepage.merchandising.updated" in controller),
("merchandising routes are permission protected","name('homepage-merchandising.index')" in routes and name_update(routes)),
("admin menu exposes featured merchandising","admin.homepage-merchandising.index" in layout),
("admin view lets operator select categories and products","categories[]" in admin and "products[]" in admin),
("admin view preserves selected products outside current pagination page","visibleProductIds" in admin and 'type=\"hidden\" name=\"products[]\"' in admin),
("homepage queries curated categories with safe fallback","where('is_homepage_featured', true)" in store and "$featuredCategories->isNotEmpty()" in store),
("homepage queries featured products through public visibility scope","$featuredProducts = $this->publicProducts()" in store and "where('is_homepage_featured', true)" in store),
("homepage renders featured products using shared product card","featuredProducts" in home and "partials.storefront-product-card" in home),
("revision snapshot captures featured categories and products","'featured_categories' =>" in revision and "'featured_products' =>" in revision),
("revision publish restores featured selections","foreach (($snapshot['featured_categories'] ?? [])" in revision and "foreach (($snapshot['featured_products'] ?? [])" in revision),
("missing SiteAppearanceController route import is fixed","SiteAppearanceController;" in routes),
("view composer shares site appearance across public pages","View::composer(" in provider and "SiteAppearanceSetting::allValues()" in provider),
("shared header supports logo and announcement","market-announcement" in header and "market-brand-logo" in header),
("shared footer supports contact and localized copy","market-footer-contact" in footer and "footer_copyright" in footer),
]
failed=[]
for label,ok in checks:
 print(("PASS: " if ok else "FAIL: ")+label)
 if not ok: failed.append(label)
if failed: print("FAILED:",failed);sys.exit(1)
print(f"PASS: {len(checks)} beta 32 merchandising/layout checks.")

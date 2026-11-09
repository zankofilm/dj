#!/usr/bin/env python3
from pathlib import Path
import sys
base=Path(__file__).resolve().parents[1]
migration=(base/"database/migrations/2026_01_01_000022_create_static_pages.php").read_text(encoding="utf-8")
page_model=(base/"app/Models/StaticPage.php").read_text(encoding="utf-8")
page_controller=(base/"app/Http/Controllers/StaticPageController.php").read_text(encoding="utf-8")
admin_controller=(base/"app/Http/Controllers/Admin/StaticPageManagementController.php").read_text(encoding="utf-8")
appearance_controller=(base/"app/Http/Controllers/Admin/SiteAppearanceController.php").read_text(encoding="utf-8")
routes=(base/"routes/web.php").read_text(encoding="utf-8")
appearance_form=(base/"resources/views/admin/appearance/edit.blade.php").read_text(encoding="utf-8")
header=(base/"resources/views/partials/storefront-header.blade.php").read_text(encoding="utf-8")
footer=(base/"resources/views/partials/storefront-footer.blade.php").read_text(encoding="utf-8")
admin_form=(base/"resources/views/admin/pages/form.blade.php").read_text(encoding="utf-8")
admin_list=(base/"resources/views/admin/pages/index.blade.php").read_text(encoding="utf-8")
public_view=(base/"resources/views/pages/show.blade.php").read_text(encoding="utf-8")
sidebar=(base/"resources/views/layouts/admin.blade.php").read_text(encoding="utf-8")
provider=(base/"app/Providers/MarketplaceServiceProvider.php").read_text(encoding="utf-8")
checks=[
("static pages migration defines localized content and publication status","Schema::create('static_pages'" in migration and "$table->json('translations')" in migration and "$table->string('status'" in migration),
("static pages have unique slug and SEO metadata","$table->string('slug', 120)->unique()" in migration and "$table->json('seo')->nullable()" in migration),
("static page model casts translations and SEO","'translations' => 'array'" in page_model and "'seo' => 'array'" in page_model),
("public page controller only serves published pages","where('status', 'published')" in page_controller and "whereNotNull('published_at')" in page_controller),
("admin page controller enforces pages.publish permission","hasPermission('pages.publish')" in admin_controller),
("admin page controller supports create update publish and delete","function store(" in admin_controller and "function update(" in admin_controller and "function destroy(" in admin_controller),
("admin page content validates fa and stores ku and en","'title_fa'" in admin_controller and "'title_ku'" in admin_controller and "'title_en'" in admin_controller and "'body_ku'" in admin_controller and "'body_en'" in admin_controller),
("page bodies are stored as text and escaped in public output","'body' => $data['body_fa']" in admin_controller and ">{{ $body }}</div>" in public_view and "white-space:pre-wrap" in public_view),
("page mutations are audited","static_page.created" in admin_controller and "static_page.updated" in admin_controller and "static_page.deleted" in admin_controller),
("public and admin page routes are registered","name('pages.show')" in routes and "name('pages.index')" in routes and "name('pages.store')" in routes and "name('pages.update')" in routes),
("admin pages route has numeric model binding constraint","whereNumber('page')" in routes),
("admin sidebar exposes static pages to authorized users","admin.pages.index" in sidebar and "pages.publish" in sidebar),
("appearance form supports five header links and eight footer links","@for($i=0;$i<5;$i++)" in appearance_form and "@for($i=0;$i<8;$i++)" in appearance_form),
("appearance controller stores localized navigation links","'navigation' =>" in appearance_controller and "'header' =>" in appearance_controller and "'footer' =>" in appearance_controller),
("navigation sanitizer permits internal paths and HTTPS only","str_starts_with($url, '/') " in appearance_controller and "https://" in appearance_controller and "str_starts_with($url, '//')" in appearance_controller),
("header renders localized configurable menu","navigation.header" in header and "labels.'.$siteLocale" in header),
("footer renders configurable links","navigation.footer" in footer and "market-footer-links" in footer),
("public static page uses shared header and footer","partials.storefront-header" in public_view and "partials.storefront-footer" in public_view),
("public page gets site appearance from view composer","'pages.show'" in provider and "SiteAppearanceSetting::allValues()" in provider),
]
failed=[]
for label,ok in checks:
 print(("PASS: " if ok else "FAIL: ")+label)
 if not ok: failed.append(label)
if failed: print("FAILED:",failed);sys.exit(1)
print(f"PASS: {len(checks)} beta 33 navigation/page checks.")

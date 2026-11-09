#!/usr/bin/env python3
from pathlib import Path
import sys
base=Path(__file__).resolve().parents[1]
migration=(base/"database/migrations/2026_01_01_000018_create_homepage_shelf_settings.php").read_text(encoding="utf-8")
model=(base/"app/Models/HomepageShelfSetting.php").read_text(encoding="utf-8")
controller=(base/"app/Http/Controllers/Admin/HomepageShelfController.php").read_text(encoding="utf-8")
routes=(base/"routes/web.php").read_text(encoding="utf-8")
seeder=(base/"database/seeders/AccessControlSeeder.php").read_text(encoding="utf-8")
layout=(base/"resources/views/layouts/admin.blade.php").read_text(encoding="utf-8")
form=(base/"resources/views/admin/homepage-shelves/index.blade.php").read_text(encoding="utf-8")
store=(base/"app/Http/Controllers/StorefrontController.php").read_text(encoding="utf-8")
view=(base/"resources/views/storefront.blade.php").read_text(encoding="utf-8")
js=(base/"resources/js/app.js").read_text(encoding="utf-8")
checks=[
("settings table has unique stable shelf key","$table->string('shelf_key', 40)->unique()" in migration),
("four default shelves are seeded","['latest'" in migration and "['popular'" in migration and "['cheapest'" in migration and "['discount'" in migration),
("translations are stored as JSON","$table->json('translations')" in migration and "'translations' => 'array'" in model),
("settings include active flag, count, order and speed","is_active" in migration and "item_limit" in migration and "sort_order" in migration and "motion_speed" in migration),
("localized title fallback is implemented","titleFor(string $locale)" in model and "'fa.title'" in model),
("management routes are registered","name('homepage-shelves.index')" in routes and "name('homepage-shelves.update')" in routes),
("management route has dedicated permission","permission:homepage.manage" in routes),
("permission is seeded","'homepage.manage' =>" in seeder),
("admin navigation exposes shelf settings","admin.homepage-shelves.index" in layout),
("admin form exposes all four shelves","'latest' =>" in form and "'popular' =>" in form and "'cheapest' =>" in form and "'discount' =>" in form),
("admin form supports enable, order and product count","[is_active]" in form and "[sort_order]" in form and "[item_limit]" in form),
("admin form supports speed and three locales","[motion_speed]" in form and "title_fa" in form and "title_ku" in form and "title_en" in form),
("controller validates all four known shelf keys","self::KEYS" in controller and "sort($submittedKeys)" in controller),
("controller constrains item count and speed","'between:4,24'" in controller and "'between:10,100'" in controller),
("controller writes audit entries","homepage.shelf.settings_updated" in controller and "AuditLog::create" in controller),
("storefront loads shelf settings","HomepageShelfSetting::query()" in store and "$shelfSettings" in store),
("product query limits are driven by admin settings","$shelfLimit('latest')" in store and "$shelfLimit('popular')" in store and "$shelfLimit('cheapest')" in store and "$shelfLimit('discount')" in store),
("view hides disabled shelves and sorts enabled shelves","!$setting->is_active" in view and "sortBy('sort_order')" in view),
("view binds configured motion speed","data-speed=" in view and "$shelf['speed']" in view),
("JavaScript applies configured motion speed","shelf.dataset.speed" in js and "delta * 0.035 * speed" in js),
]
failed=[]
for label,ok in checks:
 print(("PASS: " if ok else "FAIL: ")+label)
 if not ok: failed.append(label)
if failed:
 print("FAILED:",failed);sys.exit(1)
print(f"PASS: {len(checks)} homepage shelf CMS checks.")

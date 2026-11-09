#!/usr/bin/env python3
from pathlib import Path
import sys
base=Path(__file__).resolve().parents[1]
migration=(base/"database/migrations/2026_01_01_000019_create_site_appearance_settings.php").read_text(encoding="utf-8")
model=(base/"app/Models/SiteAppearanceSetting.php").read_text(encoding="utf-8")
controller=(base/"app/Http/Controllers/Admin/SiteAppearanceController.php").read_text(encoding="utf-8")
routes=(base/"routes/web.php").read_text(encoding="utf-8")
seeder=(base/"database/seeders/AccessControlSeeder.php").read_text(encoding="utf-8")
layout=(base/"resources/views/layouts/admin.blade.php").read_text(encoding="utf-8")
form=(base/"resources/views/admin/appearance/edit.blade.php").read_text(encoding="utf-8")
store=(base/"app/Http/Controllers/StorefrontController.php").read_text(encoding="utf-8")
view=(base/"resources/views/storefront.blade.php").read_text(encoding="utf-8")
header=(base/"resources/views/partials/storefront-header.blade.php").read_text(encoding="utf-8")
footer=(base/"resources/views/partials/storefront-footer.blade.php").read_text(encoding="utf-8")
css=(base/"resources/css/app.css").read_text(encoding="utf-8")
checks=[
("settings table has unique key and JSON value","$table->string('setting_key', 80)->unique()" in migration and "$table->json('value')" in migration),
("migration seeds brand, announcement, footer and contact defaults","'brand' =>" in migration and "'announcement' =>" in migration and "'footer_tagline' =>" in migration and "'contact' =>" in migration),
("model exposes decoded values","'value' => 'array'" in model and "allValues()" in model),
("admin permission is enforced in controller","hasPermission('appearance.manage')" in controller),
("dedicated permission is registered","'appearance.manage' =>" in seeder),
("admin routes are registered and protected","name('appearance.edit')" in routes and "name('appearance.update')" in routes and "permission:appearance.manage" in routes),
("admin navigation links to appearance settings","admin.appearance.edit" in layout),
("form supports all three brand locales","brand_fa" in form and "brand_ku" in form and "brand_en" in form),
("form supports announcement, footer and contacts","announcement_fa" in form and "footer_tagline_fa" in form and "contact_email" in form and "instagram_url" in form),
("logo upload is constrained to raster image types","'mimes:jpg,jpeg,png,webp'" in controller and "'max:2048'" in controller),
("logo replacement cleans old image after successful save","if ($newLogoPath && $oldLogoPath)" in controller and "delete($oldLogoPath)" in controller),
("failed transaction cleans newly uploaded image","if ($newLogoPath) Storage::disk('public')->delete($newLogoPath)" in controller),
("appearance changes are audited","site.appearance.updated" in controller and "AuditLog::create" in controller),
("storefront controller loads appearance settings","SiteAppearanceSetting::allValues()" in store and "'siteAppearance'" in store),
("storefront header renders localized brand and logo","$brandName" in header and "$siteLogo" in header),
("storefront announcement bar is configurable","$announcement" in header and "market-announcement" in header),
("storefront footer renders tagline, contact and copyright","footer_tagline" in footer and "market-footer-contact" in footer and "footer_copyright" in footer),
("logo, contact links and external links have safe rel attributes",'rel="noopener noreferrer"' in footer and "mailto:" in footer and "tel:" in footer),
("styles include responsive brand and footer behavior","market-brand-logo" in css and "@media(max-width:640px)" in css),
]
failed=[]
for label,ok in checks:
 print(("PASS: " if ok else "FAIL: ")+label)
 if not ok: failed.append(label)
if failed: print("FAILED:",failed);sys.exit(1)
print(f"PASS: {len(checks)} site appearance CMS checks.")

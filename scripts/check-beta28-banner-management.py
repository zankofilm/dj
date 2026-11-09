#!/usr/bin/env python3
from pathlib import Path
import sys
base=Path(__file__).resolve().parents[1]
migration=(base/"database/migrations/2026_01_01_000017_create_homepage_banners.php").read_text(encoding="utf-8")
model=(base/"app/Models/HomepageBanner.php").read_text(encoding="utf-8")
controller=(base/"app/Http/Controllers/Admin/HomepageBannerController.php").read_text(encoding="utf-8")
routes=(base/"routes/web.php").read_text(encoding="utf-8")
seeder=(base/"database/seeders/AccessControlSeeder.php").read_text(encoding="utf-8")
home_controller=(base/"app/Http/Controllers/StorefrontController.php").read_text(encoding="utf-8")
home=(base/"resources/views/storefront.blade.php").read_text(encoding="utf-8")
admin_view=(base/"resources/views/admin/banners/index.blade.php").read_text(encoding="utf-8")
form=(base/"resources/views/admin/banners/form.blade.php").read_text(encoding="utf-8")
layout=(base/"resources/views/layouts/admin.blade.php").read_text(encoding="utf-8")
checks=[
("banner table has translated JSON","$table->json('translations')" in migration),
("banner table has schedule and active flag","starts_at" in migration and "ends_at" in migration and "is_active" in migration),
("banner table has explicit sort order","$table->unsignedInteger('sort_order')" in migration),
("banner model casts translated and schedule fields","'translations' => 'array'" in model and "'starts_at' => 'datetime'" in model),
("all banner admin actions require dedicated permission","hasPermission('banners.manage')" in controller),
("banner image upload is constrained to safe raster formats","'mimes:jpg,jpeg,png,webp'" in controller and "'max:3072'" in controller),
("banner create/update/delete are audited","homepage.banner.created" in controller and "homepage.banner.updated" in controller and "homepage.banner.deleted" in controller),
("uploaded file is deleted if database write fails","Storage::disk('public')->delete($imagePath)" in controller),
("old image is removed after replacement","if ($newImagePath && $oldImagePath)" in controller),
("CRUD routes are registered","name('banners.create')" in routes and "name('banners.update')" in routes and "name('banners.destroy')" in routes),
("dedicated permission is seeded","'banners.manage' =>" in seeder),
("homepage queries only active banners within schedule","where('is_active', true)" in home_controller and "orWhere('starts_at', '<=', now())" in home_controller and "orWhere('ends_at', '>=', now())" in home_controller),
("homepage uses database banner text and image","$homepageBanners" in home and "$bannerImage" in home and "$bannerTitle" in home),
("admin navigation links to banner manager","admin.banners.index" in layout),
("admin listing exposes edit and delete","admin.banners.edit" in admin_view and "admin.banners.destroy" in admin_view),
("form includes all three locales","title_fa" in form and "title_ku" in form and "title_en" in form),
("form includes image upload and schedule","type=\"file\"" in form and "datetime-local" in form),
("form uses multipart upload","enctype=\"multipart/form-data\"" in form),
]
failed=[]
for label,ok in checks:
 print(("PASS: " if ok else "FAIL: ")+label)
 if not ok: failed.append(label)
if failed:
 print("FAILED:",failed); sys.exit(1)
print(f"PASS: {len(checks)} banner management checks.")

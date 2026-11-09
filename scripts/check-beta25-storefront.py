#!/usr/bin/env python3
from pathlib import Path
import sys
base=Path(__file__).resolve().parents[1]
controller=(base/"app/Http/Controllers/StorefrontController.php").read_text(encoding="utf-8")
routes=(base/"routes/web.php").read_text(encoding="utf-8")
home=(base/"resources/views/storefront.blade.php").read_text(encoding="utf-8")
detail=(base/"resources/views/products/show.blade.php").read_text(encoding="utf-8")
css=(base/"resources/css/app.css").read_text(encoding="utf-8")
js=(base/"resources/js/app.js").read_text(encoding="utf-8")
checks=[
("home lists only active/published products","whereIn('status', ['active', 'published'])" in controller),
("home requires approved seller","whereHas('seller'" in controller),
("home requires published timestamp","whereNotNull('published_at')" in controller),
("home eager-loads media seller category and active variants", "with(['media', 'seller', 'category', 'variants'" in controller),
("product detail uses public UUID lookup","where('public_id', $publicId)" in controller),
("product detail excludes unpublished products","->whereNotNull('published_at')" in controller),
("product detail requires approved seller","->whereHas('seller'" in controller),
("related products exclude current product","where('id', '<>', $product->id)" in controller),
("product detail route constrains UUID","whereUuid('publicId')" in routes),
("home renders product image and link","product-card-image" in home and "products.show" in home),
("home displays seller and price","$product->seller->display_name" in home and "$product->base_price_minor" in home),
("home and detail include RTL/LTR direction","['fa', 'ku']" in home and "['fa', 'ku']" in detail),
("detail includes canonical and description metadata","rel=\"canonical\"" in detail and "name=\"description\"" in detail),
("detail renders gallery thumbnails","data-gallery-image" in detail),
("thumbnail switching JS exists","mainImage.src = button.dataset.galleryImage" in js),
("responsive product grid exists","@media (max-width:640px)" in css and "grid-template-columns:repeat(2" in css),
("description output is escaped","nl2br(e($description))" in detail),
]
failed=[]
for label,ok in checks:
 print(("PASS: " if ok else "FAIL: ")+label)
 if not ok: failed.append(label)
if failed:
 print("FAILED:",failed); sys.exit(1)
print(f"PASS: {len(checks)} storefront checks.")

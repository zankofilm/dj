#!/usr/bin/env python3
from pathlib import Path
import sys
base=Path(__file__).resolve().parents[1]
controller=(base/"app/Http/Controllers/Seller/ProductController.php").read_text(encoding="utf-8")
routes=(base/"routes/web.php").read_text(encoding="utf-8")
view=(base/"resources/views/seller/products/index.blade.php").read_text(encoding="utf-8")
checks=[
("media primary action verifies seller ownership","$product->seller_id === (int) $seller->id" in controller),
("media primary action verifies product-media relation","$media->product_id === (int) $product->id" in controller),
("primary action clears old primary flags","$product->media()->lockForUpdate()->update(['is_primary' => false])" in controller),
("delete action verifies seller ownership","abort_unless((int) $product->seller_id === (int) $seller->id, 404);" in controller),
("delete action verifies media belongs to product","abort_unless((int) $media->product_id === (int) $product->id, 404);" in controller),
("deleting primary selects a replacement","$next = $product->media()->orderBy('sort_order')->orderBy('id')->first();" in controller),
("deleted file removed from public disk","Storage::disk('public')->delete($path)" in controller),
("primary route is PATCH","Route::patch('/{product}/media/{media}/primary'" in routes),
("delete route is DELETE","Route::delete('/{product}/media/{media}'" in routes),
("gallery images are rendered lazily",'loading="lazy"' in view),
("seller can choose primary image","seller.products.media.primary" in view),
("seller can delete image","seller.products.media.destroy" in view),
]
failed=[]
for label,ok in checks:
    print(("PASS: " if ok else "FAIL: ")+label)
    if not ok: failed.append(label)
if failed:
    print("FAILED:",failed); sys.exit(1)
print(f"PASS: {len(checks)} media management checks.")

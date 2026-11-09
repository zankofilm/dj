#!/usr/bin/env python3
from pathlib import Path
import sys
base=Path(__file__).resolve().parents[1]
request=(base/"app/Http/Requests/StoreProductRequest.php").read_text(encoding="utf-8")
controller=(base/"app/Http/Controllers/Seller/ProductController.php").read_text(encoding="utf-8")
view=(base/"resources/views/seller/products/create.blade.php").read_text(encoding="utf-8")
route=(base/"routes/web.php").read_text(encoding="utf-8")
checks=[
("maximum five images","'images' => ['nullable', 'array', 'max:5']" in request),
("each image capped at 5 MB","'max:5120'" in request),
("only JPEG PNG and WebP accepted","'mimes:jpg,jpeg,png,webp'" in request),
("actual image MIME types checked","'mimetypes:image/jpeg,image/png,image/webp'" in request),
("SVG is not accepted","'image/svg+xml'" not in request),
("randomized framework storage API used","->store('products/'.$seller->id, 'public')" in controller),
("media metadata persisted","$product->media()->create([" in controller),
("failed transaction files are cleaned up","Storage::disk('public')->delete($path)" in controller),
("upload form uses multipart encoding",'enctype="multipart/form-data"' in view),
("seller upload endpoint is throttled","throttle:10,1" in route),
("uploaded paths deny executable extensions",(base/"storage/app/public/.htaccess").is_file()),
]
failed=[]
for label,ok in checks:
    print(("PASS: " if ok else "FAIL: ")+label)
    if not ok: failed.append(label)
if failed:
    print("FAILED:",failed); sys.exit(1)
print(f"PASS: {len(checks)} product media security checks.")

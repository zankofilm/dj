#!/usr/bin/env python3
from pathlib import Path
import sys
base=Path(__file__).resolve().parents[1]
controller=(base/"app/Http/Controllers/Seller/ProductController.php").read_text(encoding="utf-8")
routes=(base/"routes/web.php").read_text(encoding="utf-8")
view=(base/"resources/views/seller/products/index.blade.php").read_text(encoding="utf-8")
js=(base/"resources/js/app.js").read_text(encoding="utf-8")
checks=[
("alt text has three locale fields","alt_translations.fa" in controller and "alt_translations.ku" in controller and "alt_translations.en" in controller),
("alt text length is bounded","alt_translations.en' => ['nullable', 'string', 'max:250']" in controller),
("alt changes are audited","catalog.product_media.alt_updated" in controller),
("reorder IDs must be distinct","'media_ids.*' => ['required', 'integer', 'distinct']" in controller),
("reorder list has max five images","'media_ids' => ['required', 'array', 'min:1', 'max:5']" in controller),
("submitted media set must exactly match current set","$currentIds === $submittedIds" in controller),
("reorder occurs in a transaction","DB::transaction(function () use ($request, $product, $data, $seller)" in controller),
("seller ownership is rechecked under lock","$lockedProduct->seller_id === (int) $seller->id" in controller),
("reorder action is audited","catalog.product_media.reordered" in controller),
("alt route is PATCH","Route::patch('/{product}/media/{media}/alt'" in routes),
("reorder route is PATCH","Route::patch('/{product}/media/reorder'" in routes),
("all three alt inputs appear in seller UI","alt_translations[fa]" in view and "alt_translations[ku]" in view and "alt_translations[en]" in view),
("gallery supports native drag and drop","addEventListener('dragstart'" in js and "addEventListener('dragover'" in js),
("keyboard reorder controls exist","data-move-up" in view and "data-move-down" in view and "addEventListener('click'" in js),
("reorder form rebuilds ordered ID payload","name = 'media_ids[]'" in js),
]
failed=[]
for label,ok in checks:
 print(("PASS: " if ok else "FAIL: ")+label)
 if not ok: failed.append(label)
if failed:
 print("FAILED:",failed); sys.exit(1)
print(f"PASS: {len(checks)} media workflow checks.")

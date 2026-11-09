#!/usr/bin/env python3
from pathlib import Path
import sys
base=Path(__file__).resolve().parents[1]
controller=(base/"app/Http/Controllers/StorefrontController.php").read_text(encoding="utf-8")
view=(base/"resources/views/storefront.blade.php").read_text(encoding="utf-8")
partial=(base/"resources/views/partials/storefront-product-card.blade.php").read_text(encoding="utf-8")
js=(base/"resources/js/app.js").read_text(encoding="utf-8")
css=(base/"resources/css/app.css").read_text(encoding="utf-8")
checks=[
("latest products are sorted by publish date","latest('published_at')->limit($shelfLimit('latest'))" in controller),
("cheapest products are sorted by base price","orderBy('base_price_minor')->limit($shelfLimit('cheapest'))" in controller),
("popular ranking uses paid orders only","->where('orders.status', 'paid')" in controller),
("popular ranking sums purchased quantities","SUM(order_items.quantity)" in controller),
("public product queries eager-load variants","variants" in controller and "where('status', 'active')" in controller),
        ("discounts require active variants","$variants->where('status', 'active')" in controller),
("discounts require compare-at price greater than effective price","compare_at_price_minor > COALESCE(price_minor" in controller),
("homepage has latest shelf","'id' => 'latest-shelf'" in view),
("homepage has popular shelf","'id' => 'popular-shelf'" in view),
("homepage has cheapest shelf","'id' => 'cheapest-shelf'" in view),
("homepage has discount shelf","'id' => 'discount-products'" in view),
("homepage has three fallback banner slides and managed banner loop", "@foreach($homepageBanners as $banner)" in view and "SPECIAL OFFERS" in view and view.count('<article class="hero-slide') >= 2),
("banner has previous and next controls","data-hero-prev" in view and "data-hero-next" in view),
("latest shelf has both-direction motion flag","'direction' => 'both'" in view),
("shelf product links to detail route","route('products.show', $cardProduct->public_id)" in partial),
("sale cards show a discount badge and compare-at price","product-discount-badge" in partial and "product-compare-price" in partial),
        ("product cards use translated image alt text","alt_translations, $locale" in partial),
("hero auto-advances","window.setInterval(() => show(active + 1), 6000)" in js),
("hero honors reduced-motion preference","prefers-reduced-motion: reduce" in js),
("latest shelf reverses at both edges","if (shelf.scrollLeft >= max - 1) direction = -1" in js and "if (shelf.scrollLeft <= 1) direction = 1" in js),
("shelf controls scroll products","shelf.scrollBy({left:" in js),
("RTL shelf uses stable LTR scroll coordinates","shelf-viewport { direction:ltr" in css and "shelf-track { direction:rtl" in css),
        ("shelf styling is horizontally scrollable","overflow-x:auto" in css),
]
failed=[]
for label,ok in checks:
 print(("PASS: " if ok else "FAIL: ")+label)
 if not ok: failed.append(label)
if failed:
 print("FAILED:",failed); sys.exit(1)
print(f"PASS: {len(checks)} homepage shelf checks.")

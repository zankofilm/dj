#!/usr/bin/env python3
from pathlib import Path
import sys
base=Path(__file__).resolve().parents[1]
migration=(base/"database/migrations/2026_01_01_000020_create_homepage_design_revisions.php").read_text(encoding="utf-8")
model=(base/"app/Models/HomepageDesignRevision.php").read_text(encoding="utf-8")
controller=(base/"app/Http/Controllers/Admin/HomepageDesignRevisionController.php").read_text(encoding="utf-8")
routes=(base/"routes/web.php").read_text(encoding="utf-8")
layout=(base/"resources/views/layouts/admin.blade.php").read_text(encoding="utf-8")
listing=(base/"resources/views/admin/homepage-revisions/index.blade.php").read_text(encoding="utf-8")
preview=(base/"resources/views/admin/homepage-revisions/preview.blade.php").read_text(encoding="utf-8")
checks=[
("revision table stores title, status and JSON snapshot","$table->string('title', 140)" in migration and "$table->json('snapshot')" in migration and "$table->string('status', 20)" in migration),
("revision records track creator, publisher and publish time","created_by" in migration and "published_by" in migration and "published_at" in migration),
("revision model casts snapshot and timestamp","'snapshot' => 'array'" in model and "'published_at' => 'datetime'" in model),
("revision controller enforces homepage permission","hasPermission('homepage.manage')" in controller),
("snapshot captures shelves, appearance and banners","'shelves' => HomepageShelfSetting::" in controller and "'appearance' => SiteAppearanceSetting::" in controller and "'banners' => HomepageBanner::" in controller),
("preview and publish routes are registered","name('homepage-revisions.preview')" in routes and "name('homepage-revisions.publish')" in routes),
("create, list and delete routes are registered","name('homepage-revisions.store')" in routes and "name('homepage-revisions.index')" in routes and "name('homepage-revisions.destroy')" in routes),
("all revision routes require homepage permission","routes.count('permission:homepage.manage') >= 5"),
("admin menu exposes revision history","admin.homepage-revisions.index" in layout),
("listing can create snapshots and browse history","homepage-revisions.store" in listing and "homepage-revisions.preview" in listing),
("preview shows localized brand, banner and shelves","$brand" in preview and "$banners" in preview and "$shelves" in preview),
("publishing requires explicit confirmation","confirm_publish" in controller and "confirm_publish" in preview),
("publishing restores all three configuration domains","HomepageShelfSetting::updateOrCreate" in controller and "SiteAppearanceSetting::updateOrCreate" in controller and "HomepageBanner::updateOrCreate" in controller),
("publishing is transactional and row-locked","DB::transaction" in controller and "lockForUpdate" in controller),
("rollback can republish an older published snapshot","revision->status === 'published'" in controller and "published" in preview),
("revision lifecycle is audited","homepage.revision.created" in controller and "homepage.revision.published" in controller and "homepage.revision.deleted" in controller),
("published revisions cannot be deleted","abort_if($revision->status === 'published'" in controller),
]
failed=[]
for label,ok in checks:
 print(("PASS: " if ok else "FAIL: ")+label)
 if not ok: failed.append(label)
if failed: print("FAILED:",failed);sys.exit(1)
print(f"PASS: {len(checks)} homepage revision checks.")

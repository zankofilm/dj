<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\HomepageBanner;
use App\Models\HomepageDesignRevision;
use App\Models\HomepageShelfSetting;
use App\Models\SiteAppearanceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomepageDesignRevisionController extends Controller
{
    private function authorizeManager(Request $request): void
    {
        abort_unless($request->user()?->hasPermission('homepage.manage'), 403);
    }

    public function index(Request $request): View
    {
        $this->authorizeManager($request);
        $revisions = HomepageDesignRevision::query()
            ->with(['creator:id,name', 'publisher:id,name'])
            ->latest('id')
            ->paginate(20);

        return view('admin.homepage-revisions.index', compact('revisions'));
    }

    public function store(Request $request)
    {
        $this->authorizeManager($request);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:140'],
        ]);

        $snapshot = $this->captureSnapshot();
        $revision = HomepageDesignRevision::create([
            'title' => $data['title'],
            'status' => 'draft',
            'snapshot' => $snapshot,
            'created_by' => $request->user()->id,
        ]);

        $this->audit($request, 'homepage.revision.created', $revision, null, [
            'title' => $revision->title,
            'status' => $revision->status,
            'snapshot_hash' => hash('sha256', json_encode($snapshot, JSON_UNESCAPED_UNICODE)),
        ]);

        return redirect()->route('admin.homepage-revisions.preview', $revision)
            ->with('status', 'نسخه پیش‌نمایش ذخیره شد. برای اعمال آن باید انتشار را تأیید کنید.');
    }

    public function preview(Request $request, HomepageDesignRevision $revision): View
    {
        $this->authorizeManager($request);

        return view('admin.homepage-revisions.preview', compact('revision'));
    }

    public function publish(Request $request, HomepageDesignRevision $revision)
    {
        $this->authorizeManager($request);
        abort_unless($request->boolean('confirm_publish'), 422, 'تأیید انتشار الزامی است.');

        DB::transaction(function () use ($request, $revision) {
            $revision = HomepageDesignRevision::query()->lockForUpdate()->findOrFail($revision->id);
            $snapshot = $revision->snapshot;
            abort_unless(
                is_array($snapshot)
                && isset($snapshot['shelves'], $snapshot['appearance'], $snapshot['banners'])
                && is_array($snapshot['shelves'])
                && is_array($snapshot['appearance'])
                && is_array($snapshot['banners'])
                && is_array($snapshot['featured_categories'] ?? [])
                && is_array($snapshot['featured_products'] ?? []),
                422,
                'ساختار نسخه طراحی معتبر نیست.'
            );

            foreach ($snapshot['shelves'] as $row) {
                if (!isset($row['shelf_key'])) continue;
                HomepageShelfSetting::updateOrCreate(
                    ['shelf_key' => $row['shelf_key']],
                    [
                        'translations' => $row['translations'] ?? [],
                        'is_active' => (bool) ($row['is_active'] ?? false),
                        'item_limit' => max(4, min(24, (int) ($row['item_limit'] ?? 12))),
                        'sort_order' => max(1, min(99, (int) ($row['sort_order'] ?? 1))),
                        'motion_speed' => max(10, min(100, (int) ($row['motion_speed'] ?? 35))),
                    ]
                );
            }

            foreach ($snapshot['appearance'] as $row) {
                if (!isset($row['setting_key'])) continue;
                SiteAppearanceSetting::updateOrCreate(
                    ['setting_key' => $row['setting_key']],
                    ['value' => $row['value'] ?? []]
                );
            }

            Category::query()->update(['is_homepage_featured' => false, 'homepage_sort_order' => 0]);
            foreach (($snapshot['featured_categories'] ?? []) as $index => $categoryId) {
                Category::whereKey((int) $categoryId)->update([
                    'is_homepage_featured' => true,
                    'homepage_sort_order' => $index + 1,
                ]);
            }
            Product::query()->update(['is_homepage_featured' => false, 'homepage_sort_order' => 0]);
            foreach (($snapshot['featured_products'] ?? []) as $index => $productId) {
                Product::whereKey((int) $productId)->update([
                    'is_homepage_featured' => true,
                    'homepage_sort_order' => $index + 1,
                ]);
            }

            $bannerIds = [];
            foreach ($snapshot['banners'] as $row) {
                $id = $row['id'] ?? null;
                $attributes = [
                    'image_path' => $row['image_path'] ?? null,
                    'translations' => $row['translations'] ?? [],
                    'target_url' => $row['target_url'] ?? null,
                    'sort_order' => max(0, (int) ($row['sort_order'] ?? 0)),
                    'is_active' => (bool) ($row['is_active'] ?? false),
                    'starts_at' => $row['starts_at'] ?? null,
                    'ends_at' => $row['ends_at'] ?? null,
                    'created_by' => $row['created_by'] ?? null,
                ];
                if ($id) {
                    HomepageBanner::updateOrCreate(['id' => $id], $attributes);
                    $bannerIds[] = (int) $id;
                } else {
                    HomepageBanner::create($attributes);
                }
            }
            $bannerQuery = HomepageBanner::query();
            if ($bannerIds) $bannerQuery->whereNotIn('id', $bannerIds);
            $bannerQuery->delete();

            $beforeStatus = $revision->status;
            $revision->update([
                'status' => 'published',
                'published_by' => $request->user()->id,
                'published_at' => now(),
            ]);

            $this->audit($request, 'homepage.revision.published', $revision, [
                'status' => $beforeStatus,
            ], [
                'status' => 'published',
                'published_at' => $revision->published_at?->toISOString(),
                'snapshot_id' => $revision->id,
            ]);
        });

        return redirect()->route('admin.homepage-revisions.index')
            ->with('status', 'نسخه طراحی منتشر شد. برای بازگشت، نسخه قبلی را از تاریخچه انتخاب و دوباره منتشر کنید.');
    }

    public function destroy(Request $request, HomepageDesignRevision $revision)
    {
        $this->authorizeManager($request);
        abort_if($revision->status === 'published', 409, 'نسخه منتشرشده از تاریخچه حذف نمی‌شود.');
        $this->audit($request, 'homepage.revision.deleted', $revision, [
            'title' => $revision->title,
            'status' => $revision->status,
        ], null);
        $revision->delete();

        return redirect()->route('admin.homepage-revisions.index')
            ->with('status', 'پیش‌نویس حذف شد.');
    }

    private function captureSnapshot(): array
    {
        return [
            'schema_version' => 1,
            'captured_at' => now()->toISOString(),
            'shelves' => HomepageShelfSetting::query()->orderBy('shelf_key')->get()->toArray(),
            'appearance' => SiteAppearanceSetting::query()->orderBy('setting_key')->get()->toArray(),
            'banners' => HomepageBanner::query()->orderBy('sort_order')->orderBy('id')->get()->toArray(),
            'featured_categories' => Category::query()->where('is_homepage_featured', true)->orderBy('homepage_sort_order')->pluck('id')->all(),
            'featured_products' => Product::query()->where('is_homepage_featured', true)->orderBy('homepage_sort_order')->pluck('id')->all(),
        ];
    }

    private function audit(Request $request, string $action, HomepageDesignRevision $revision, ?array $before, ?array $after): void
    {
        AuditLog::create([
            'actor_user_id' => $request->user()->id,
            'action' => $action,
            'subject_type' => HomepageDesignRevision::class,
            'subject_id' => (string) $revision->id,
            'before_data' => $before,
            'after_data' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 2000),
            'created_at' => now(),
        ]);
    }
}

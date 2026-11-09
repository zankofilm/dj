<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\HomepageBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class HomepageBannerController extends Controller
{
    private function authorizeManager(Request $request): void
    {
        abort_unless($request->user()?->hasPermission('banners.manage'), 403);
    }

    public function index(Request $request): View
    {
        $this->authorizeManager($request);
        $banners = HomepageBanner::query()->orderBy('sort_order')->orderByDesc('id')->paginate(20);

        return view('admin.banners.index', compact('banners'));
    }

    public function create(Request $request): View
    {
        $this->authorizeManager($request);

        return view('admin.banners.form', ['banner' => new HomepageBanner()]);
    }

    public function store(Request $request)
    {
        $this->authorizeManager($request);
        $data = $this->validated($request, true);
        $imagePath = $request->file('image')?->store('homepage-banners', 'public');

        try {
            DB::transaction(function () use ($request, $data, $imagePath) {
                $banner = HomepageBanner::create([
                    ...$data,
                    'image_path' => $imagePath,
                    'created_by' => $request->user()->id,
                ]);
                $this->audit($request, 'homepage.banner.created', $banner, null, $banner->only([
                    'id', 'is_active', 'sort_order', 'starts_at', 'ends_at',
                ]));
            });
        } catch (\Throwable $exception) {
            if ($imagePath) Storage::disk('public')->delete($imagePath);
            throw $exception;
        }

        return redirect()->route('admin.banners.index')->with('status', 'بنر جدید ذخیره شد.');
    }

    public function edit(Request $request, HomepageBanner $banner): View
    {
        $this->authorizeManager($request);

        return view('admin.banners.form', compact('banner'));
    }

    public function update(Request $request, HomepageBanner $banner)
    {
        $this->authorizeManager($request);
        $data = $this->validated($request, false);
        $newImagePath = $request->file('image')?->store('homepage-banners', 'public');
        $oldImagePath = $banner->image_path;

        try {
            DB::transaction(function () use ($request, $banner, $data, $newImagePath) {
                $before = $banner->only(['is_active', 'sort_order', 'starts_at', 'ends_at', 'target_url']);
                $banner->update($data + ($newImagePath ? ['image_path' => $newImagePath] : []));
                $this->audit($request, 'homepage.banner.updated', $banner, $before, $banner->only([
                    'is_active', 'sort_order', 'starts_at', 'ends_at', 'target_url',
                ]));
            });
        } catch (\Throwable $exception) {
            if ($newImagePath) Storage::disk('public')->delete($newImagePath);
            throw $exception;
        }

        if ($newImagePath && $oldImagePath) Storage::disk('public')->delete($oldImagePath);

        return redirect()->route('admin.banners.index')->with('status', 'بنر به‌روزرسانی شد.');
    }

    public function destroy(Request $request, HomepageBanner $banner)
    {
        $this->authorizeManager($request);
        $oldImagePath = $banner->image_path;

        DB::transaction(function () use ($request, $banner) {
            $before = $banner->only(['is_active', 'sort_order', 'starts_at', 'ends_at', 'target_url']);
            $this->audit($request, 'homepage.banner.deleted', $banner, $before, null);
            $banner->delete();
        });

        if ($oldImagePath) Storage::disk('public')->delete($oldImagePath);

        return redirect()->route('admin.banners.index')->with('status', 'بنر حذف شد.');
    }

    private function validated(Request $request, bool $imageRequired): array
    {
        $data = $request->validate([
            'title_fa' => ['required', 'string', 'max:160'],
            'title_ku' => ['nullable', 'string', 'max:160'],
            'title_en' => ['nullable', 'string', 'max:160'],
            'subtitle_fa' => ['nullable', 'string', 'max:500'],
            'subtitle_ku' => ['nullable', 'string', 'max:500'],
            'subtitle_en' => ['nullable', 'string', 'max:500'],
            'button_fa' => ['nullable', 'string', 'max:60'],
            'button_ku' => ['nullable', 'string', 'max:60'],
            'button_en' => ['nullable', 'string', 'max:60'],
            'target_url' => ['nullable', 'url', 'max:2048'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'image' => [$imageRequired ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ], [
            'image.required' => 'انتخاب تصویر بنر الزامی است.',
            'image.image' => 'فایل انتخاب‌شده تصویر معتبر نیست.',
            'image.mimes' => 'فرمت تصویر باید JPG، PNG یا WebP باشد.',
            'image.max' => 'حجم تصویر نباید بیشتر از ۳ مگابایت باشد.',
            'ends_at.after_or_equal' => 'زمان پایان نمی‌تواند قبل از زمان شروع باشد.',
        ]);

        if (!empty($data['starts_at']) && !empty($data['ends_at'])
            && strtotime($data['ends_at']) < strtotime($data['starts_at'])) {
            throw ValidationException::withMessages(['ends_at' => 'بازه زمانی بنر معتبر نیست.']);
        }

        return [
            'translations' => [
                'fa' => ['title' => $data['title_fa'], 'subtitle' => $data['subtitle_fa'] ?? '', 'button' => $data['button_fa'] ?? 'مشاهده'],
                'ku' => ['title' => $data['title_ku'] ?: $data['title_fa'], 'subtitle' => $data['subtitle_ku'] ?? '', 'button' => $data['button_ku'] ?: ($data['button_fa'] ?? 'بینین')],
                'en' => ['title' => $data['title_en'] ?: $data['title_fa'], 'subtitle' => $data['subtitle_en'] ?? '', 'button' => $data['button_en'] ?: ($data['button_fa'] ?? 'Explore')],
            ],
            'target_url' => $data['target_url'] ?? null,
            'sort_order' => $data['sort_order'],
            'is_active' => $request->boolean('is_active'),
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
        ];
    }

    private function audit(Request $request, string $action, HomepageBanner $banner, ?array $before, ?array $after): void
    {
        AuditLog::create([
            'actor_user_id' => $request->user()->id,
            'action' => $action,
            'subject_type' => HomepageBanner::class,
            'subject_id' => (string) $banner->id,
            'before_data' => $before,
            'after_data' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 2000),
            'created_at' => now(),
        ]);
    }
}

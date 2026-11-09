<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\HomepageShelfSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HomepageShelfController extends Controller
{
    private const KEYS = ['latest', 'popular', 'cheapest', 'discount'];

    private function authorizeManager(Request $request): void
    {
        abort_unless($request->user()?->hasPermission('homepage.manage'), 403);
    }

    public function index(Request $request): View
    {
        $this->authorizeManager($request);
        $settings = HomepageShelfSetting::query()->orderBy('sort_order')->get()->keyBy('shelf_key');

        return view('admin.homepage-shelves.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $this->authorizeManager($request);

        $data = $request->validate([
            'shelves' => ['required', 'array'],
            'shelves.*.is_active' => ['nullable', 'boolean'],
            'shelves.*.sort_order' => ['required', 'integer', 'min:1', 'max:99'],
            'shelves.*.item_limit' => ['required', 'integer', 'between:4,24'],
            'shelves.*.motion_speed' => ['required', 'integer', 'between:10,100'],
            'shelves.*.title_fa' => ['required', 'string', 'max:100'],
            'shelves.*.title_ku' => ['nullable', 'string', 'max:100'],
            'shelves.*.title_en' => ['nullable', 'string', 'max:100'],
        ]);

        $submittedKeys = array_keys($data['shelves']);
        sort($submittedKeys);
        $expectedKeys = self::KEYS;
        sort($expectedKeys);
        abort_unless($submittedKeys === $expectedKeys, 422, 'ساختار تنظیمات نوارها معتبر نیست.');

        DB::transaction(function () use ($request, $data) {
            foreach (self::KEYS as $key) {
                $setting = HomepageShelfSetting::where('shelf_key', $key)->lockForUpdate()->firstOrFail();
                $before = $setting->only(['translations', 'is_active', 'item_limit', 'sort_order', 'motion_speed']);
                $input = $data['shelves'][$key];
                $setting->update([
                    'translations' => [
                        'fa' => ['title' => $input['title_fa'], 'eyebrow' => data_get($before, 'translations.fa.eyebrow', strtoupper($key))],
                        'ku' => ['title' => $input['title_ku'] ?: $input['title_fa'], 'eyebrow' => data_get($before, 'translations.ku.eyebrow', strtoupper($key))],
                        'en' => ['title' => $input['title_en'] ?: $input['title_fa'], 'eyebrow' => data_get($before, 'translations.en.eyebrow', strtoupper($key))],
                    ],
                    'is_active' => $request->boolean("shelves.{$key}.is_active"),
                    'item_limit' => $input['item_limit'],
                    'sort_order' => $input['sort_order'],
                    'motion_speed' => $input['motion_speed'],
                ]);
                AuditLog::create([
                    'actor_user_id' => $request->user()->id,
                    'action' => 'homepage.shelf.settings_updated',
                    'subject_type' => HomepageShelfSetting::class,
                    'subject_id' => (string) $setting->id,
                    'before_data' => $before,
                    'after_data' => $setting->only(['translations', 'is_active', 'item_limit', 'sort_order', 'motion_speed']),
                    'ip_address' => $request->ip(),
                    'user_agent' => substr((string) $request->userAgent(), 0, 2000),
                    'created_at' => now(),
                ]);
            }
        });

        return redirect()->route('admin.homepage-shelves.index')->with('status', 'تنظیمات نوارهای صفحه اصلی ذخیره شد.');
    }
}

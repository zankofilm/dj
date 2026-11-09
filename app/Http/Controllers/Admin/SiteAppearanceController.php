<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SiteAppearanceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SiteAppearanceController extends Controller
{
    private function authorizeManager(Request $request): void
    {
        abort_unless($request->user()?->hasPermission('appearance.manage'), 403);
    }

    public function edit(Request $request): View
    {
        $this->authorizeManager($request);
        $settings = SiteAppearanceSetting::allValues();

        return view('admin.appearance.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $this->authorizeManager($request);
        $data = $request->validate([
            'brand_fa' => ['required','string','max:80'],
            'brand_ku' => ['nullable','string','max:80'],
            'brand_en' => ['nullable','string','max:80'],
            'announcement_fa' => ['nullable','string','max:180'],
            'announcement_ku' => ['nullable','string','max:180'],
            'announcement_en' => ['nullable','string','max:180'],
            'footer_tagline_fa' => ['nullable','string','max:250'],
            'footer_tagline_ku' => ['nullable','string','max:250'],
            'footer_tagline_en' => ['nullable','string','max:250'],
            'footer_copyright_fa' => ['nullable','string','max:160'],
            'footer_copyright_ku' => ['nullable','string','max:160'],
            'footer_copyright_en' => ['nullable','string','max:160'],
            'contact_email' => ['nullable','email','max:190'],
            'contact_phone' => ['nullable','string','max:40'],
            'instagram_url' => ['nullable','url','max:2048'],
            'telegram_url' => ['nullable','url','max:2048'],
            'header_links' => ['nullable','array','max:5'],
            'footer_links' => ['nullable','array','max:8'],
            'header_links.*.label_fa' => ['nullable','string','max:80'],
            'header_links.*.label_ku' => ['nullable','string','max:80'],
            'header_links.*.label_en' => ['nullable','string','max:80'],
            'header_links.*.url' => ['nullable','string','max:2048'],
            'footer_links.*.label_fa' => ['nullable','string','max:80'],
            'footer_links.*.label_ku' => ['nullable','string','max:80'],
            'footer_links.*.label_en' => ['nullable','string','max:80'],
            'footer_links.*.url' => ['nullable','string','max:2048'],
            'logo' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ], [
            'logo.image' => 'فایل لوگو باید تصویر معتبر باشد.',
            'logo.mimes' => 'فرمت لوگو باید JPG، PNG یا WebP باشد.',
            'logo.max' => 'حجم لوگو نباید بیشتر از ۲ مگابایت باشد.',
        ]);

        $oldLogo = SiteAppearanceSetting::where('setting_key','logo')->first();
        $oldLogoPath = is_array($oldLogo?->value) ? ($oldLogo->value['path'] ?? null) : null;
        $newLogoPath = $request->file('logo')?->store('site-branding','public');

        $values = [
            'brand' => [
                'fa'=>$data['brand_fa'],
                'ku'=>$data['brand_ku'] ?: $data['brand_fa'],
                'en'=>$data['brand_en'] ?: $data['brand_fa'],
            ],
            'announcement' => [
                'fa'=>$data['announcement_fa'] ?? '',
                'ku'=>$data['announcement_ku'] ?? ($data['announcement_fa'] ?? ''),
                'en'=>$data['announcement_en'] ?? ($data['announcement_fa'] ?? ''),
            ],
            'footer_tagline' => [
                'fa'=>$data['footer_tagline_fa'] ?? '',
                'ku'=>$data['footer_tagline_ku'] ?? ($data['footer_tagline_fa'] ?? ''),
                'en'=>$data['footer_tagline_en'] ?? ($data['footer_tagline_fa'] ?? ''),
            ],
            'footer_copyright' => [
                'fa'=>$data['footer_copyright_fa'] ?? '',
                'ku'=>$data['footer_copyright_ku'] ?? ($data['footer_copyright_fa'] ?? ''),
                'en'=>$data['footer_copyright_en'] ?? ($data['footer_copyright_fa'] ?? ''),
            ],
            'contact' => [
                'email'=>$data['contact_email'] ?? '',
                'phone'=>$data['contact_phone'] ?? '',
                'instagram'=>$data['instagram_url'] ?? '',
                'telegram'=>$data['telegram_url'] ?? '',
            ],
            'navigation' => [
                'header' => $this->cleanLinks($data['header_links'] ?? [], 5),
                'footer' => $this->cleanLinks($data['footer_links'] ?? [], 8),
            ],
        ];

        try {
            DB::transaction(function () use ($request, $values, $newLogoPath, $oldLogoPath) {
                foreach ($values as $key => $value) {
                    $row = SiteAppearanceSetting::firstOrNew(['setting_key'=>$key]);
                    $before = $row->exists ? $row->value : null;
                    $row->value = $value;
                    $row->save();
                    $this->audit($request, $key, $before, $value);
                }

                if ($newLogoPath) {
                    $row = SiteAppearanceSetting::firstOrNew(['setting_key'=>'logo']);
                    $before = $row->exists ? $row->value : null;
                    $row->value = ['path'=>$newLogoPath];
                    $row->save();
                    $this->audit($request, 'logo', $before, $row->value);
                }
            });
        } catch (\Throwable $exception) {
            if ($newLogoPath) Storage::disk('public')->delete($newLogoPath);
            throw $exception;
        }

        if ($newLogoPath && $oldLogoPath) Storage::disk('public')->delete($oldLogoPath);

        return redirect()->route('admin.appearance.edit')->with('status','تنظیمات هویت سایت ذخیره شد.');
    }

    private function cleanLinks(array $links, int $limit): array
    {
        $clean = [];
        foreach (array_slice($links, 0, $limit) as $link) {
            $url = trim((string) ($link['url'] ?? ''));
            $isInternal = str_starts_with($url, '/') && !str_starts_with($url, '//') && !str_contains($url, '\\') && !preg_match('/[\\r\\n]/', $url);
            $isHttps = str_starts_with(strtolower($url), 'https://') && filter_var($url, FILTER_VALIDATE_URL) !== false;
            if (!$url || (!$isInternal && !$isHttps)) continue;

            $fa = trim((string) ($link['label_fa'] ?? ''));
            if ($fa === '') continue;
            $clean[] = [
                'labels' => [
                    'fa' => $fa,
                    'ku' => trim((string) ($link['label_ku'] ?? '')) ?: $fa,
                    'en' => trim((string) ($link['label_en'] ?? '')) ?: $fa,
                ],
                'url' => $url,
            ];
        }
        return $clean;
    }

    private function audit(Request $request, string $key, mixed $before, mixed $after): void
    {
        AuditLog::create([
            'actor_user_id'=>$request->user()->id,
            'action'=>'site.appearance.updated',
            'subject_type'=>SiteAppearanceSetting::class,
            'subject_id'=>$key,
            'before_data'=>['value'=>$before],
            'after_data'=>['value'=>$after],
            'ip_address'=>$request->ip(),
            'user_agent'=>substr((string)$request->userAgent(),0,2000),
            'created_at'=>now(),
        ]);
    }
}

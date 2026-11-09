<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\StaticPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaticPageManagementController extends Controller
{
    private function authorizeEditor(Request $request): void
    {
        abort_unless($request->user()?->hasPermission('pages.publish'), 403);
    }

    public function index(Request $request): View
    {
        $this->authorizeEditor($request);
        $pages = StaticPage::query()->latest('updated_at')->paginate(25);
        return view('admin.pages.index', compact('pages'));
    }

    public function create(Request $request): View
    {
        $this->authorizeEditor($request);
        $page = new StaticPage(['status' => 'draft', 'translations' => []]);
        return view('admin.pages.form', compact('page'));
    }

    public function store(Request $request)
    {
        $this->authorizeEditor($request);
        $data = $this->validated($request);
        $page = DB::transaction(function () use ($request, $data) {
            $page = StaticPage::create([
                'slug' => $data['slug'],
                'translations' => $this->translations($data),
                'content_blocks' => $this->contentBlocks($data['blocks'] ?? []),
                'seo' => ['title' => $data['seo_title'] ?? '', 'description' => $data['seo_description'] ?? ''],
                'status' => $data['status'],
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
                'published_at' => $data['status'] === 'published' ? now() : null,
            ]);
            $this->audit($request, 'static_page.created', $page, null, ['slug' => $page->slug, 'status' => $page->status]);
            return $page;
        });
        return redirect()->route('admin.pages.edit', $page)->with('status', 'صفحه ذخیره شد.');
    }

    public function edit(Request $request, StaticPage $page): View
    {
        $this->authorizeEditor($request);
        return view('admin.pages.form', compact('page'));
    }

    public function update(Request $request, StaticPage $page)
    {
        $this->authorizeEditor($request);
        $data = $this->validated($request, $page);
        $before = ['slug' => $page->slug, 'status' => $page->status];
        $page->update([
            'slug' => $data['slug'],
            'translations' => $this->translations($data),
            'content_blocks' => $this->contentBlocks($data['blocks'] ?? []),
            'seo' => ['title' => $data['seo_title'] ?? '', 'description' => $data['seo_description'] ?? ''],
            'status' => $data['status'],
            'updated_by' => $request->user()->id,
            'published_at' => $data['status'] === 'published' ? ($page->published_at ?? now()) : null,
        ]);
        $this->audit($request, 'static_page.updated', $page, $before, ['slug' => $page->slug, 'status' => $page->status]);
        return back()->with('status', 'صفحه به‌روزرسانی شد.');
    }

    public function destroy(Request $request, StaticPage $page)
    {
        $this->authorizeEditor($request);
        $before = ['slug' => $page->slug, 'status' => $page->status];
        $this->audit($request, 'static_page.deleted', $page, $before, null);
        $page->delete();
        return redirect()->route('admin.pages.index')->with('status', 'صفحه حذف شد.');
    }

    private function validated(Request $request, ?StaticPage $page = null): array
    {
        return $request->validate([
            'slug' => ['required', 'alpha_dash', 'max:120', Rule::unique('static_pages', 'slug')->ignore($page?->id)],
            'title_fa' => ['required', 'string', 'max:180'],
            'title_ku' => ['nullable', 'string', 'max:180'],
            'title_en' => ['nullable', 'string', 'max:180'],
            'body_fa' => ['required', 'string', 'max:100000'],
            'body_ku' => ['nullable', 'string', 'max:100000'],
            'body_en' => ['nullable', 'string', 'max:100000'],
            'seo_title' => ['nullable', 'string', 'max:180'],
            'seo_description' => ['nullable', 'string', 'max:320'],
            'blocks' => ['nullable', 'array', 'max:8'],
            'blocks.*.type' => ['nullable', Rule::in(['heading', 'paragraph', 'notice'])],
            'blocks.*.text_fa' => ['nullable', 'string', 'max:5000'],
            'blocks.*.text_ku' => ['nullable', 'string', 'max:5000'],
            'blocks.*.text_en' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['draft', 'published'])],
        ]);
    }

    private function contentBlocks(array $blocks): array
    {
        $result = [];
        foreach (array_slice($blocks, 0, 8) as $block) {
            $type = $block['type'] ?? null;
            $fa = trim((string) ($block['text_fa'] ?? ''));
            $ku = trim((string) ($block['text_ku'] ?? ''));
            $en = trim((string) ($block['text_en'] ?? ''));
            if (!$type || $fa === '') {
                continue;
            }
            $result[] = [
                'type' => $type,
                'text' => [
                    'fa' => $fa,
                    'ku' => $ku !== '' ? $ku : $fa,
                    'en' => $en !== '' ? $en : $fa,
                ],
            ];
        }
        return $result;
    }

    private function translations(array $data): array
    {
        return [
            'fa' => ['title' => $data['title_fa'], 'body' => $data['body_fa']],
            'ku' => ['title' => $data['title_ku'] ?: $data['title_fa'], 'body' => $data['body_ku'] ?: $data['body_fa']],
            'en' => ['title' => $data['title_en'] ?: $data['title_fa'], 'body' => $data['body_en'] ?: $data['body_fa']],
        ];
    }

    private function audit(Request $request, string $action, StaticPage $page, ?array $before, ?array $after): void
    {
        AuditLog::create([
            'actor_user_id' => $request->user()->id,
            'action' => $action,
            'subject_type' => StaticPage::class,
            'subject_id' => (string) $page->id,
            'before_data' => $before,
            'after_data' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 2000),
            'created_at' => now(),
        ]);
    }
}

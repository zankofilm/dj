#!/usr/bin/env python3
from pathlib import Path
import sys
base=Path(__file__).resolve().parents[1]
migration=(base/'database/migrations/2026_01_01_000023_add_content_blocks_to_static_pages.php').read_text(encoding='utf-8')
model=(base/'app/Models/StaticPage.php').read_text(encoding='utf-8')
controller=(base/'app/Http/Controllers/Admin/StaticPageManagementController.php').read_text(encoding='utf-8')
form=(base/'resources/views/admin/pages/form.blade.php').read_text(encoding='utf-8')
public=(base/'resources/views/pages/show.blade.php').read_text(encoding='utf-8')
checks=[
('migration adds nullable JSON content blocks',"$table->json('content_blocks')->nullable()" in migration),
('migration has reversible drop column',"$table->dropColumn('content_blocks')" in migration),
('model casts and allows mass assignment of blocks',"'content_blocks' => 'array'" in model and "'content_blocks'" in model.split('protected $fillable')[1].split(';')[0]),
('controller persists blocks on create and update',controller.count("'content_blocks' => $this->contentBlocks") == 2),
('controller caps block count at eight',"'blocks' => ['nullable', 'array', 'max:8']" in controller),
('controller limits block types to allowlist',"Rule::in(['heading', 'paragraph', 'notice'])" in controller),
('controller caps localized text length',controller.count("'string', 'max:5000'") >= 3),
('controller drops empty blocks and localizes fallback',"if (!$type || $fa === '')" in controller and "'ku' => $ku !== '' ? $ku : $fa" in controller and "'en' => $en !== '' ? $en : $fa" in controller),
('admin editor exposes eight ordered slots',"@for($i=0;$i<8;$i++)" in form and 'blocks[{{ $i }}][type]' in form),
('admin editor exposes all three locales',all(f'text_{locale}' in form for locale in ['fa','ku','en'])),
('public page renders heading paragraph and notice types',all(f"===''{t}''" in public or f"===''{t}''".replace("''", "'") in public for t in ['heading','paragraph','notice'])),
('public page escapes block content instead of rendering raw HTML','{{ $blockText }}' in public and '{!! $blockText !!}' not in public),
('public page retains body-only fallback for older content',"$body=$translation['body']??''" in public and "@if(trim($body)!=='')" in public),
]
failed=[]
for label,ok in checks:
 print(('PASS: ' if ok else 'FAIL: ')+label)
 if not ok: failed.append(label)
if failed:
 print('FAILED:',failed);sys.exit(1)
print(f'PASS: {len(checks)} beta 34 page-builder checks.')

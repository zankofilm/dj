@extends('layouts.admin')
@section('title','صفحات ثابت سایت')
@section('content')
<div class="page-head"><div><h1>صفحات ثابت سایت</h1><div class="muted">صفحات درباره ما، تماس با ما، راهنمای خرید، قوانین و حریم خصوصی را مدیریت و منتشر کنید.</div></div><a class="btn" href="{{ route('admin.pages.create') }}">ایجاد صفحه جدید</a></div>
@if(session('status'))<div class="flash">{{ session('status') }}</div>@endif
<section class="panel"><div class="table-wrap"><table><thead><tr><th>عنوان</th><th>نشانی</th><th>وضعیت</th><th>آخرین تغییر</th><th>عملیات</th></tr></thead><tbody>
@forelse($pages as $page)
<tr>
<td><strong>{{ data_get($page->translations,'fa.title') }}</strong><div class="muted">{{ data_get($page->translations,'en.title') }}</div></td>
<td dir="ltr">/pages/{{ $page->slug }}</td>
<td><span class="pill {{ $page->status==='published'?'good':'warn' }}">{{ $page->status==='published'?'منتشرشده':'پیش‌نویس' }}</span></td>
<td>{{ $page->updated_at?->format('Y-m-d H:i') }}</td>
<td><div class="actions"><a class="btn light" href="{{ route('admin.pages.edit',$page) }}">ویرایش</a>@if($page->status==='published')<a class="btn light" target="_blank" rel="noopener" href="{{ route('pages.show',$page->slug) }}">نمایش</a>@endif
<form method="POST" action="{{ route('admin.pages.destroy',$page) }}" onsubmit="return confirm('این صفحه حذف شود؟')">@csrf @method('DELETE')<button class="btn danger" type="submit">حذف</button></form></div></td>
</tr>
@empty<tr><td colspan="5">هنوز صفحه‌ای ایجاد نشده است.</td></tr>@endforelse
</tbody></table></div><div style="margin-top:14px">{{ $pages->links() }}</div></section>
@endsection

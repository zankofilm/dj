@extends('layouts.admin')
@section('title','نسخه‌ها و انتشار هوم‌پیج')
@section('content')
<div class="page-head">
    <div><h1>نسخه‌ها و انتشار هوم‌پیج</h1><div class="muted">از تنظیمات فعلی یک نسخه ذخیره کنید، پیش‌نمایش بگیرید و در صورت تأیید منتشر کنید. نسخه‌های منتشرشده برای Rollback نگهداری می‌شوند.</div></div>
    <span class="pill">History · Preview · Rollback</span>
</div>
@if(session('status'))<div class="flash">{{ session('status') }}</div>@endif
@if($errors->any())<div class="flash" style="background:#fff0ee;color:#b42318">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<section class="panel">
    <h2>ذخیره نسخه جدید</h2>
    <form method="POST" action="{{ route('admin.homepage-revisions.store') }}" class="revision-create">
        @csrf
        <label>عنوان نسخه<input name="title" required maxlength="140" placeholder="مثلاً طراحی ویژه کمپین پاییز"></label>
        <button class="btn" type="submit">ذخیره و ساخت پیش‌نمایش</button>
    </form>
    <p class="muted">این عملیات از بنرها، چهار نوار محصول و تنظیمات هویت سایت Snapshot می‌گیرد و هنوز چیزی را در سایت تغییر نمی‌دهد.</p>
</section>
<section class="panel">
    <h2>تاریخچه طراحی</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>عنوان</th><th>وضعیت</th><th>ایجادکننده</th><th>تاریخ</th><th>انتشار</th><th>عملیات</th></tr></thead>
            <tbody>
            @forelse($revisions as $revision)
                <tr>
                    <td><strong>{{ $revision->title }}</strong><div class="muted">نسخه #{{ $revision->id }}</div></td>
                    <td>@if($revision->status === 'published')<span class="pill good">منتشرشده</span>@else<span class="pill warn">پیش‌نویس</span>@endif</td>
                    <td>{{ $revision->creator?->name ?? 'کاربر حذف‌شده' }}</td>
                    <td>{{ $revision->created_at?->format('Y-m-d H:i') }}</td>
                    <td>{{ $revision->published_at?->format('Y-m-d H:i') ?? '—' }}</td>
                    <td><div class="actions">
                        <a class="btn light" href="{{ route('admin.homepage-revisions.preview',$revision) }}">پیش‌نمایش</a>
                        @if($revision->status !== 'published')
                            <form method="POST" action="{{ route('admin.homepage-revisions.destroy',$revision) }}" onsubmit="return confirm('این پیش‌نویس حذف شود؟')">@csrf @method('DELETE')<button class="btn danger" type="submit">حذف</button></form>
                        @endif
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="6">هنوز نسخه‌ای ذخیره نشده است.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $revisions->links() }}</div>
</section>
<style>
.revision-create{display:flex;align-items:end;gap:14px;flex-wrap:wrap}.revision-create label{display:grid;gap:7px;min-width:min(420px,100%);font-size:12px}.revision-create input{width:100%}
@media(max-width:640px){.revision-create{display:grid}.revision-create label{min-width:0}}
</style>
@endsection

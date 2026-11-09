@extends('layouts.admin')
@section('title','مدیریت بنرهای صفحه اصلی')
@section('content')
<div class="page-head">
    <div><h1>بنرهای صفحه اصلی</h1><div class="muted">مدیریت تصویر، متن سه‌زبانه، ترتیب و زمان نمایش اسلایدر</div></div>
    <a class="btn" href="{{ route('admin.banners.create') }}">＋ افزودن بنر</a>
</div>
<div class="panel">
    <div class="table-wrap">
        <table>
            <thead><tr><th>تصویر</th><th>عنوان فارسی</th><th>ترتیب</th><th>وضعیت</th><th>زمان نمایش</th><th>عملیات</th></tr></thead>
            <tbody>
            @forelse($banners as $banner)
                <tr>
                    <td>
                        @if($banner->image_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($banner->image_path) }}" alt="" style="width:110px;height:58px;object-fit:cover;border-radius:8px">
                        @else — @endif
                    </td>
                    <td>{{ data_get($banner->translations,'fa.title','—') }}</td>
                    <td>{{ $banner->sort_order }}</td>
                    <td>
                        @if($banner->isVisibleNow())<span class="pill good">در حال نمایش</span>
                        @elseif($banner->is_active)<span class="pill warn">فعال / خارج از بازه</span>
                        @else<span class="pill">غیرفعال</span>@endif
                    </td>
                    <td class="muted">{{ $banner->starts_at?->format('Y-m-d H:i') ?? 'بدون شروع' }}<br>{{ $banner->ends_at?->format('Y-m-d H:i') ?? 'بدون پایان' }}</td>
                    <td><div class="actions">
                        <a class="btn light" href="{{ route('admin.banners.edit',$banner) }}">ویرایش</a>
                        <form method="POST" action="{{ route('admin.banners.destroy',$banner) }}" onsubmit="return confirm('بنر حذف شود؟')">
                            @csrf @method('DELETE')<button class="btn danger" type="submit">حذف</button>
                        </form>
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="6">هنوز بنری ثبت نشده است. برای شروع، یک بنر اضافه کنید.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $banners->links() }}</div>
</div>
@endsection

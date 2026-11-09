@extends('layouts.admin')
@section('title','مدیریت محصولات')
@section('content')
<div class="page-head"><div><h1>محصولات و کاتالوگ</h1><div class="muted">بررسی وضعیت انتشار محصولات فروشندگان</div></div><span class="pill">{{ number_format($products->total()) }} محصول</span></div>
<form method="GET" class="actions" style="margin-bottom:16px"><input name="q" value="{{ request('q') }}" placeholder="شناسه مسیر محصول"><select name="status"><option value="">همه وضعیت‌ها</option>@foreach(['draft','pending_review','submitted','active','published','rejected','suspended'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ $status }}</option>@endforeach</select><button class="btn">اعمال فیلتر</button><a class="btn light" href="{{ route('admin.catalog.index') }}">پاک‌کردن</a></form>
<div class="panel"><div class="table-wrap"><table><thead><tr><th>مسیر محصول</th><th>فروشنده</th><th>قیمت پایه</th><th>وضعیت</th><th>تغییر وضعیت</th></tr></thead><tbody>
@forelse($products as $product)<tr><td>{{ $product->slug }}</td><td>{{ $product->seller?->display_name ?? '—' }}</td><td>{{ number_format($product->base_price_minor) }} {{ $product->currency }}</td><td><span class="pill">{{ $product->status }}</span></td><td><form method="POST" action="{{ route('admin.catalog.status',$product) }}" class="actions">@csrf @method('PATCH')<select name="status">@foreach(['draft','active','published','rejected','suspended'] as $status)<option value="{{ $status }}" @selected($product->status===$status)>{{ $status }}</option>@endforeach</select><input name="reason" placeholder="دلیل (اختیاری)" style="width:130px"><button class="btn light">ثبت</button></form></td></tr>
@empty<tr><td colspan="5">محصولی یافت نشد.</td></tr>@endforelse
</tbody></table></div><div style="margin-top:16px">{{ $products->links() }}</div></div>
@endsection

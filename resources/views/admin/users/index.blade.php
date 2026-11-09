@extends('layouts.admin')
@section('title','مدیریت کاربران')
@section('content')
<div class="page-head"><div><h1>مدیریت کاربران</h1><div class="muted">جست‌وجو، مشاهده نقش‌ها و کنترل وضعیت حساب‌ها</div></div><span class="pill">{{ number_format($users->total()) }} کاربر</span></div>
<form method="GET" class="actions" style="margin-bottom:16px"><input name="q" value="{{ request('q') }}" placeholder="نام یا ایمیل کاربر"><button class="btn" type="submit">جست‌وجو</button><a class="btn light" href="{{ route('admin.users.index') }}">پاک‌کردن</a></form>
<div class="panel"><div class="table-wrap"><table><thead><tr><th>نام</th><th>ایمیل</th><th>نقش‌ها</th><th>وضعیت</th><th>تاریخ عضویت</th><th>عملیات</th></tr></thead><tbody>
@forelse($users as $user)<tr><td>{{ $user->name }}</td><td>{{ $user->email }}</td><td>{{ $user->roles->pluck('name')->join('، ') ?: '—' }}</td><td><span class="pill {{ $user->status==='active'?'good':($user->status==='suspended'?'bad':'warn') }}">{{ $user->status }}</span></td><td>{{ $user->created_at?->format('Y/m/d') }}</td><td>
@if(!request()->user()->is($user))
<form method="POST" action="{{ route('admin.users.status',$user) }}" class="actions">@csrf @method('PATCH')
<select name="status"><option value="active" @selected($user->status==='active')>فعال</option><option value="pending" @selected($user->status==='pending')>در انتظار</option><option value="suspended" @selected($user->status==='suspended')>تعلیق</option></select><button class="btn light" type="submit">ذخیره</button></form>
@else<span class="muted">حساب جاری</span>@endif
</td></tr>@empty<tr><td colspan="6">کاربری پیدا نشد.</td></tr>@endforelse
</tbody></table></div><div style="margin-top:16px">{{ $users->links() }}</div></div>
@endsection

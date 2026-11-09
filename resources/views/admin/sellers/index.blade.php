@extends('layouts.admin')
@section('title','مدیریت فروشندگان')
@section('content')
<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['fa', 'ku']) ? 'rtl' : 'ltr' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>مدیریت درخواست فروشندگان</title></head>
<body>
<main class="form-shell">
    <h1>مدیریت درخواست فروشندگان</h1>
    @if(session('status')) <div class="notice">{{ session('status') }}</div> @endif
    @foreach($sellers as $seller)
        <section class="seller-row">
            <h2>{{ $seller->display_name }}</h2>
            <p>{{ $seller->owner->email }} — {{ $seller->status }}</p>
            <form method="POST" action="{{ route('admin.sellers.decide', $seller) }}">
                @csrf
                <select name="decision" required>
                    <option value="approve">تأیید</option>
                    <option value="changes_requested">درخواست اصلاح</option>
                    <option value="reject">رد درخواست</option>
                </select>
                <input name="reason" placeholder="توضیحات (برای رد یا اصلاح الزامی است)">
                <button type="submit">ثبت تصمیم</button>
            </form>
        </section>
    @endforeach
    {{ $sellers->links() }}
</main>
</body>
</html>

@endsection

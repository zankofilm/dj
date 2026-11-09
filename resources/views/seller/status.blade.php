<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['fa', 'ku']) ? 'rtl' : 'ltr' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ __('seller.status') }}</title></head>
<body>
<main class="form-shell">
    <h1>{{ $seller->display_name }}</h1>
    <p>{{ __('seller.status') }}: {{ __('seller.'.$seller->status) }}</p>
    @if($seller->rejection_reason)
        <p>{{ $seller->rejection_reason }}</p>
    @endif
</main>
</body>
</html>

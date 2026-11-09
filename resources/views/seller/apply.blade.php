<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['fa', 'ku']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('seller.apply_title') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<main class="form-shell">
    <h1>{{ __('seller.apply_title') }}</h1>
    @if ($errors->any())
        <div class="notice error">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('seller.application.store') }}">
        @csrf
        @foreach (['display_name', 'legal_name', 'registration_number', 'country_code', 'contact_email', 'contact_phone'] as $field)
            <label for="{{ $field }}">{{ __('seller.'.$field) }}</label>
            <input id="{{ $field }}" name="{{ $field }}" value="{{ old($field) }}" @if($field === 'display_name') required @endif>
        @endforeach
        <button type="submit">{{ __('seller.submit') }}</button>
    </form>
</main>
</body>
</html>

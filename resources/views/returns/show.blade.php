<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['fa','ku']) ? 'rtl' : 'ltr' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>وضعیت مرجوعی</title></head>
<body><main class="form-shell">
<h1>پیگیری درخواست مرجوعی</h1>
<p>شماره پیگیری: {{ $return->public_id }}</p>
<p>وضعیت: {{ $return->status }}</p>
<p>مبلغ درخواستی: {{ number_format($return->requested_refund_minor) }} {{ $return->currency }}</p>
@if($return->approved_refund_minor !== null)<p>مبلغ تأییدشده: {{ number_format($return->approved_refund_minor) }} {{ $return->currency }}</p>@endif
<h2>تاریخچه</h2>
@foreach($return->events as $event)
<section class="seller-row"><strong>{{ $event->event_type }}</strong><p>{{ $event->note }}</p><small>{{ $event->created_at }}</small></section>
@endforeach
</main></body></html>

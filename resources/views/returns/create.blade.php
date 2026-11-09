<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['fa','ku']) ? 'rtl' : 'ltr' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>درخواست مرجوعی</title></head>
<body><main class="form-shell">
<h1>درخواست مرجوعی کالا</h1>
@if($errors->any())<div class="notice error">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('returns.store') }}">@csrf
<label>قلم سفارش</label>
<select name="order_item_id" required>
@foreach($order->items as $item)
<option value="{{ $item->id }}">{{ $item->product_title_snapshot }} — تعداد خرید: {{ $item->quantity }}</option>
@endforeach
</select>
<label>تعداد</label><input type="number" min="1" name="quantity" required>
<label>علت مرجوعی</label>
<select name="reason_code" required>
<option value="damaged">کالا آسیب‌دیده است</option>
<option value="wrong_item">کالای اشتباه ارسال شده</option>
<option value="not_as_described">مغایرت با توضیحات</option>
<option value="changed_mind">انصراف از خرید</option>
<option value="other">سایر</option>
</select>
<label>توضیحات</label><textarea name="note" maxlength="2000"></textarea>
<button type="submit">ثبت درخواست</button>
</form>
</main></body></html>

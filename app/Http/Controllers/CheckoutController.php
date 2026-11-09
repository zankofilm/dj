<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Services\Checkout\CheckoutException;
use App\Services\Checkout\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function show(): View
    {
        $cart = Cart::where('user_id', request()->user()->id)
            ->where('status', 'active')->with('items.variant.product')->firstOrFail();

        abort_if($cart->items->isEmpty(), 422, 'سبد خرید خالی است.');

        return view('checkout.index', compact('cart'));
    }

    public function place(Request $request, CheckoutService $checkout): RedirectResponse
    {
        $data = $request->validate([
            'recipient_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:40'],
            'address' => ['required', 'string', 'max:2000'],
            'postal_code' => ['nullable', 'string', 'max:40'],
        ]);

        $cart = Cart::where('user_id', $request->user()->id)
            ->where('status', 'active')->firstOrFail();

        try {
            $order = $checkout->place($cart, [
                ...$data,
                'locale' => app()->getLocale(),
            ]);
        } catch (CheckoutException $exception) {
            return back()->withErrors(['checkout' => $exception->getMessage()]);
        }

        return redirect()->route('orders.show', $order->public_id)
            ->with('status', 'سفارش شما ثبت شد و در انتظار پرداخت است.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    private function activeCart(int $userId): Cart
    {
        return Cart::firstOrCreate(
            ['user_id' => $userId, 'status' => 'active'],
            ['currency' => null]
        );
    }

    public function index(): View
    {
        $cart = $this->activeCart(request()->user()->id);
        $cart->load('items.variant.product');

        return view('cart.index', compact('cart'));
    }

    public function add(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $variant = ProductVariant::with('product.seller')->findOrFail($data['variant_id']);
        abort_unless(
            $variant->status === 'active'
            && $variant->product->status === 'published'
            && $variant->product->seller?->status === 'approved',
            422,
            'این کالا در حال حاضر قابل خرید نیست.'
        );

        $cart = $this->activeCart($request->user()->id);
        $existingCurrencies = $cart->items()->with('variant')->get()
            ->pluck('variant.currency')->unique()->values();

        if ($existingCurrencies->isNotEmpty() && !$existingCurrencies->contains($variant->currency)) {
            return back()->withErrors(['currency' => 'امکان ترکیب ارزهای متفاوت در یک سبد خرید وجود ندارد.']);
        }

        $item = CartItem::firstOrNew(['cart_id' => $cart->id, 'variant_id' => $variant->id]);
        $item->quantity = min(50, (int) $item->quantity + (int) $data['quantity']);
        $item->save();

        return redirect()->route('cart.index')->with('status', 'کالا به سبد خرید اضافه شد.');
    }

    public function remove(CartItem $item): RedirectResponse
    {
        abort_unless($item->cart->user_id === request()->user()->id, 404);
        $item->delete();

        return back()->with('status', 'کالا از سبد خرید حذف شد.');
    }
}

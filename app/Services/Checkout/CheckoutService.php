<?php

namespace App\Services\Checkout;

use App\Models\AuditLog;
use App\Models\Cart;
use App\Models\InventoryBalance;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SellerOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutService
{
    /**
     * Creates a pending-payment order and reserves stock atomically.
     * Payment capture is deliberately outside this service.
     */
    public function place(Cart $cart, array $customerSnapshot): Order
    {
        return DB::transaction(function () use ($cart, $customerSnapshot) {
            $lockedCart = Cart::query()->whereKey($cart->id)->lockForUpdate()->firstOrFail();
            if ($lockedCart->status !== 'active') {
                throw new CheckoutException('سبد خرید فعال نیست.');
            }

            $items = $lockedCart->items()->orderBy('variant_id')->get();
            if ($items->isEmpty()) {
                throw new CheckoutException('سبد خرید خالی است.');
            }

            $prepared = [];
            $currency = null;
            foreach ($items as $cartItem) {
                $variant = ProductVariant::query()
                    ->with(['product.seller'])
                    ->whereKey($cartItem->variant_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $product = $variant->product;
                if ($variant->status !== 'active' || $product->status !== 'published'
                    || !$product->seller || $product->seller->status !== 'approved') {
                    throw new CheckoutException('یکی از محصولات دیگر قابل خرید نیست.');
                }

                $currency ??= $variant->currency;
                if ($currency !== $variant->currency) {
                    throw new CheckoutException('برای ثبت سفارش، همه کالاها باید ارز یکسان داشته باشند.');
                }

                $balance = InventoryBalance::query()
                    ->where('variant_id', $variant->id)
                    ->lockForUpdate()
                    ->first();

                if ($variant->track_inventory && !$balance) {
                    throw new CheckoutException('اطلاعات موجودی یکی از کالاها ناقص است.');
                }

                if ($variant->track_inventory) {
                    $available = (int) $balance->on_hand - (int) $balance->reserved;
                    if ($available < (int) $cartItem->quantity) {
                        throw new CheckoutException('موجودی یکی از کالاها کافی نیست.');
                    }
                }

                $unitPrice = $variant->price_minor ?? $product->base_price_minor;
                $prepared[] = [
                    'cart_item' => $cartItem,
                    'variant' => $variant,
                    'product' => $product,
                    'balance' => $balance,
                    'unit_price' => (int) $unitPrice,
                    'line_total' => (int) $unitPrice * (int) $cartItem->quantity,
                ];
            }

            $subtotal = array_sum(array_column($prepared, 'line_total'));
            $order = Order::create([
                'public_id' => (string) Str::uuid(),
                'user_id' => $lockedCart->user_id,
                'status' => 'pending_payment',
                'currency' => $currency,
                'subtotal_minor' => $subtotal,
                'total_minor' => $subtotal,
                'customer_snapshot' => $customerSnapshot,
                'placed_at' => now(),
            ]);

            $sellerGroups = collect($prepared)->groupBy(fn ($row) => $row['product']->seller_id);
            foreach ($sellerGroups as $sellerId => $group) {
                $sellerSubtotal = array_sum(array_column($group->all(), 'line_total'));
                $sellerOrder = SellerOrder::create([
                    'public_id' => (string) Str::uuid(),
                    'order_id' => $order->id,
                    'seller_id' => $sellerId,
                    'status' => 'pending_payment',
                    'currency' => $currency,
                    'subtotal_minor' => $sellerSubtotal,
                    'shipping_minor' => 0,
                    'discount_minor' => 0,
                    'total_minor' => $sellerSubtotal,
                    'fulfillment_snapshot' => ['seller_id' => (int) $sellerId],
                ]);

                foreach ($group as $row) {
                    $variant = $row['variant'];
                    $product = $row['product'];
                    $quantity = (int) $row['cart_item']->quantity;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'seller_order_id' => $sellerOrder->id,
                        'seller_id' => $sellerId,
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'product_title_snapshot' => data_get($product->translations, 'fa.title', $product->slug),
                        'sku_snapshot' => $variant->sku,
                        'quantity' => $quantity,
                        'unit_price_minor' => $row['unit_price'],
                        'line_total_minor' => $row['line_total'],
                        'commercial_snapshot' => [
                            'currency' => $currency,
                            'product_type' => $product->product_type,
                            'condition' => $product->condition,
                            'brand_name' => $product->brand_name,
                            'variant_options' => $variant->option_values,
                            'title_translations' => data_get($product->translations, '*.title', []),
                        ],
                    ]);

                    if ($variant->track_inventory) {
                        $row['balance']->reserved += $quantity;
                        $row['balance']->version++;
                        $row['balance']->save();

                        InventoryMovement::create([
                            'variant_id' => $variant->id,
                            'movement_type' => 'reserve',
                            'quantity_delta' => -$quantity,
                            'reference_type' => Order::class,
                            'reference_id' => (string) $order->id,
                            'reason' => 'رزرو موجودی هنگام ثبت سفارش',
                            'actor_user_id' => $lockedCart->user_id,
                            'created_at' => now(),
                        ]);
                    }
                }
            }

            AuditLog::create([
                'actor_user_id' => $lockedCart->user_id,
                'action' => 'commerce.order.created',
                'subject_type' => Order::class,
                'subject_id' => (string) $order->id,
                'after_data' => [
                    'status' => $order->status,
                    'total_minor' => $order->total_minor,
                    'currency' => $currency,
                    'seller_order_count' => $sellerGroups->count(),
                ],
                'created_at' => now(),
            ]);

            $lockedCart->items()->delete();
            $lockedCart->status = 'converted';
            $lockedCart->save();

            return $order->load(['items', 'sellerOrders']);
        }, 3);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function show(string $publicId): View
    {
        $order = Order::with(['items', 'sellerOrders.seller'])
            ->where('public_id', $publicId)
            ->where('user_id', request()->user()->id)
            ->firstOrFail();

        return view('orders.show', compact('order'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->withCount('items')
            ->latest('placed_at')
            ->latest('id')
            ->paginate(10);

        $stats = [
            'total' => Order::where('user_id', $request->user()->id)->count(),
            'pending' => Order::where('user_id', $request->user()->id)->where('status', 'pending_payment')->count(),
            'completed' => Order::where('user_id', $request->user()->id)->whereIn('status', ['completed', 'delivered'])->count(),
        ];

        return view('account.index', compact('orders', 'stats'));
    }

    public function showOrder(Request $request, string $publicId): View
    {
        $order = Order::query()
            ->with(['items', 'sellerOrders.seller'])
            ->where('public_id', $publicId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return view('orders.show', compact('order'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ReturnRequest;
use App\Services\Returns\ReturnException;
use App\Services\Returns\ReturnRequestService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReturnController extends Controller
{
    public function create(string $publicId): View
    {
        $order = Order::with('items')->where('public_id', $publicId)
            ->where('user_id', request()->user()->id)->firstOrFail();

        return view('returns.create', compact('order'));
    }

    public function store(Request $request, ReturnRequestService $service)
    {
        $data = $request->validate([
            'order_item_id' => ['required', 'integer', 'exists:order_items,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason_code' => ['required', 'string', 'max:60'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $return = $service->request(
                $request->user()->id,
                $data['order_item_id'],
                $data['quantity'],
                $data['reason_code'],
                $data['note'] ?? null
            );
        } catch (ReturnException $exception) {
            return back()->withErrors(['return' => $exception->getMessage()]);
        }

        return redirect()->route('returns.show', $return->public_id)
            ->with('status', 'درخواست مرجوعی ثبت شد و در انتظار بررسی است.');
    }

    public function show(string $publicId): View
    {
        $return = ReturnRequest::with('events', 'orderItem')
            ->where('public_id', $publicId)
            ->where('customer_user_id', request()->user()->id)
            ->firstOrFail();

        return view('returns.show', compact('return'));
    }
}

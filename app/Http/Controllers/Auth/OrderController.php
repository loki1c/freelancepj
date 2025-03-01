<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\OrderUpdateRequest;

class OrderController extends Controller
{
    public function update(OrderUpdateRequest $request, Order $order)
        {
            $order->update($request->validated());
            return response()->json(['message' => 'Заказ обновлён', 'order' => $order]);
        }
}

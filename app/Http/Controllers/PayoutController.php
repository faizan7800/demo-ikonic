<?php

namespace App\Http\Controllers;

use App\Jobs\PayoutOrderJob;
use App\Models\Order;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
     /**
     * Send a payout for a specific order.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendPayout(Request $request)
    {
        // Validate the request data (you may add more validation as needed)
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        // Find the order by ID
        $order = Order::find($request->input('order_id'));

        // Dispatch the SendPayoutJob
        SendPayoutJob::dispatch($order);

        return response()->json(['message' => 'Payout job dispatched successfully']);
    }
}

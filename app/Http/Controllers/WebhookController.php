<?php

namespace App\Http\Controllers;

use App\Services\AffiliateService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function __invoke(Request $request): JsonResponse
    {
        // Log the incoming webhook payload for debugging (optional)
        $payload = $request->all();
        \Log::info('Webhook Received', ['payload' => $payload]);

        // Extract necessary information from the webhook payload
        $eventType = $payload['event_type'] ?? null;
        $data = $payload['data'] ?? null;

        // Perform actions based on the event type
        switch ($eventType) {
            case 'order.created':
                // Handle order created event
                $this->orderService->processOrder($data);

                break;

            // Add more cases based on your webhook events

            default:
                // Handle unknown event types or ignore them
                return response()->json(['message' => 'Webhook event not supported'], 400);
        }

        // Respond with a success message
        return response()->json(['message' => 'Webhook processed successfully']);
    }
}

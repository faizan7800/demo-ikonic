<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\ApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class PayoutOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Use the API service to send a payout of the correct amount.
     * Note: The order status must be paid if the payout is successful, or remain unpaid in the event of an exception.
     *
     * @return void
     */
    public function handle(ApiService $apiService)
    {
        $apiService = new ApiService(); // Replace with actual ApiService class instantiation

        $amountToSend = $this->order->commission_owed;

        // Assuming you have a method like sendPayout in ApiService
        $payoutSuccess = $apiService->sendPayout($this->order->merchant->email, $amountToSend);

        // Update the payout_status in the database based on the payout result
        $payoutStatus = $payoutSuccess ? 'completed' : 'failed';
        $this->order->update(['payout_status' => $payoutStatus]);
    }
}

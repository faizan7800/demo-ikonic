<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class OrderService
{
    protected AffiliateService $affiliateService;

    public function __construct(AffiliateService $affiliateService)
    {
        $this->affiliateService = $affiliateService;
    }

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        // Find or create affiliate based on customer_email using AffiliateService
        $affiliate = $this->affiliateService->findOrCreateAffiliate($data['customer_email']);

        // Create an order
        $order = Order::create([
            'merchant_id' => $data['merchant_id'],
            'affiliate_id' => $affiliate->id,
            'subtotal' => $data['subtotal'],
            'commission_owed' => 0, // Initially set to 0, will be updated later
            'payout_status' => Order::STATUS_UNPAID,
            'customer_email' => $data['customer_email'],
            'created_at' => now(),
        ]);

        // Calculate commission
        $commissionRate = $affiliate->commission_rate ?? $data['default_commission_rate'];
        $commissionAmount = $data['subtotal'] * ($commissionRate / 100);

        // Update commission_owed field in the order
        $order->commission_owed = $commissionAmount;

        // Log commission details using AffiliateService
        $this->affiliateService->logCommission($affiliate, $commissionAmount);

        // Save changes
        $order->save();

        return $order;
    }

    /**
     * Find or create affiliate based on customer_email.
     *
     * @param string $customerEmail
     * @return Affiliate
     */
    private function findOrCreateAffiliate(string $customerEmail)
    {
        // Check if an affiliate already exists with the given customer_email
        $affiliate = Affiliate::whereHas('user', function ($query) use ($customerEmail) {
            $query->where('email', $customerEmail);
        })->first();

        // If not, create a new affiliate
        if (!$affiliate) {
            $user = User::create([
                'email' => $customerEmail,
                'name' => 'Customer', // You can set a default name for the customer
                'password' => bcrypt(Str::random(10)), // Set a random password
                'type' => User::TYPE_AFFILIATE,
            ]);

            $affiliate = Affiliate::create([
                'user_id' => $user->id,
                'commission_rate' => 0, // Set a default commission rate if needed
            ]);
        }

        return $affiliate;
    }

    /**
     * Log commission details.
     *
     * @param Affiliate $affiliate
     * @param float $commissionAmount
     */
    private function logCommission(Affiliate $affiliate, float $commissionAmount)
    {
        // Your commission logging logic goes here
        // For example, you can create a CommissionLog model and store the details in the database
        CommissionLog::create([
            'affiliate_id' => $affiliate->id,
            'commission_amount' => $commissionAmount,
            'processed_at' => now(),
        ]);
    }
}

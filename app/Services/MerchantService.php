<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class MerchantService
{
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */
    public function register(array $data): Merchant
    {
        // Validate the data as needed

        // Create a new user
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'type' => User::TYPE_MERCHANT,
        ]);

        // Create a new merchant
        $merchant = Merchant::create([
            'user_id' => $user->id,
            'domain' => $data['domain'] ?? 'example.com',
            'display_name' => $data['display_name'] ?? 'Example Merchant',
            'turn_customers_into_affiliates' => $data['turn_customers_into_affiliates'] ?? true,
            'default_commission_rate' => $data['default_commission_rate'] ?? 10.0,
        ]);

        return $merchant;
    }

    /**
     * Update the user
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, array $data)
    {
        // Validate the data as needed

        // Ensure the user has a merchant relationship
        if ($user->merchant) {
            // Update the merchant details
            $user->merchant->update([
                'domain' => $data['domain'] ?? $user->merchant->domain,
                'display_name' => $data['display_name'] ?? $user->merchant->display_name,
                'turn_customers_into_affiliates' => $data['turn_customers_into_affiliates'] ?? $user->merchant->turn_customers_into_affiliates,
                'default_commission_rate' => $data['default_commission_rate'] ?? $user->merchant->default_commission_rate,
            ]);

            // Optionally, update user details if needed
            $user->update([
                'name' => $data['name'] ?? $user->name,
                'email' => $data['email'] ?? $user->email,
                // Add other user fields as needed
            ]);

            // Reload the user and merchant to reflect the changes
            $user->load('merchant');

            return $user;
        }

        // Handle the case where the user doesn't have a merchant relationship
        // Throwing an exception is one way to handle this case
        throw new \Exception('User does not have a merchant relationship.');

        // Alternatively, you can return a specific response or handle it based on your application logic.
    }

    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Merchant|null
     */
    public function findMerchantByEmail(string $email): ?Merchant
    {
        // Find the user by email
        $user = User::where('email', $email)->first();

        // If the user is found, return the associated merchant (if any)
        return $user ? $user->merchant : null;
    }

    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     */
    public function payout(Affiliate $affiliate)
    {
        // Get the affiliate's pending orders for payout
        $pendingOrders = $affiliate->orders()->where('payout_status', Order::STATUS_UNPAID)->get();

        // Perform the payout for each pending order
        foreach ($pendingOrders as $order) {
            // Your payout logic goes here
            // For example, update the order's payout status
            $order->update(['payout_status' => Order::STATUS_PAID]);

            
            // PayoutLog::create(['order_id' => $order->id, 'amount' => $order->commission_owed, 'paid_at' => now()]);
        }


        return $pendingOrders; // Or any other response as needed
    }

}

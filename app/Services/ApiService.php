<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * You don't need to do anything here. This is just to help
 */
class ApiService
{
    /**
     * Create a new discount code for an affiliate
     *
     * @param Merchant $merchant
     *
     * @return array{id: int, code: string}
     */
    public function createDiscountCode(Merchant $merchant): array
    {
        return [
            'id' => rand(0, 100000),
            'code' => Str::uuid()
        ];
    }

    /**
     * Send a payout to an email
     *
     * @param  string $email
     * @param  float $amount
     * @return void
     * @throws RuntimeException
     */
    public function sendPayout(string $email, float $amount): bool
    {
        try {
            // Your actual logic to send the payout goes here
            // For simplicity, let's use Laravel's Mail facade to send an email

            // Replace this with your email view and subject
            $data = ['amount' => $amount];
            Mail::to($email)->send(new \App\Mail\PayoutMail($data));

            // For the sake of this example, let's assume the payout was successful
            return true;
        } catch (\Exception $e) {
            // Log any exceptions or errors during the payout process
            \Log::error("Error sending payout to $email. Error: " . $e->getMessage());

            // Return false to indicate payout failure
            return false;
        }
    }
}

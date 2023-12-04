<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\User;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Services\ApiService;
use App\Jobs\PayoutOrderJob;
class MerchantController extends Controller
{
    protected MerchantService $merchantService;

    public function __construct(MerchantService $merchantService)
    {
        $this->MerchantService = $merchantService;
    }



    public function updateMerchant(Request $request, User $user)
    {
        try {
            $data = $request->all();

            $updatedUser = $this->merchantService->updateMerchant($user, $data);

            return response()->json(['message' => 'Merchant updated successfully', 'user' => $updatedUser]);
        } catch (\Exception $e) {
            // Handle the exception, e.g., return an error response
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    /**
     * Useful order statistics for the merchant API.
     * 
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */

     public function register(Request $request, MerchantService $merchantService)
     {
         // Validate the request data
         $request->validate([
             'name' => 'required|string',
             'email' => 'required|email|unique:users',
             'password' => 'required|min:8',
         ]);
 
         // Create a new user and associated merchant
         $data = [
             'name' => $request->input('name'),
             'email' => $request->input('email'),
             'password' => $request->input('password'),
             'domain' => $request->input('domain'), // Optional: You can retrieve these from the request
             'display_name' => $request->input('display_name'),
             'turn_customers_into_affiliates' => $request->input('turn_customers_into_affiliates'),
             'default_commission_rate' => $request->input('default_commission_rate'),
         ];
 
         $merchant = $merchantService->register($data);
 
         return response()->json(['message' => 'User and Merchant registered successfully', 'merchant' => $merchant]);
     }

     public function findMerchantByEmail(Request $request)
    {
        // Validate the request data
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->input('email');

        // Find the merchant by email
        $merchant = $this->merchantService->findMerchantByEmail($email);

        if ($merchant) {
            return response()->json(['message' => 'Merchant found', 'merchant' => $merchant]);
        } else {
            return response()->json(['message' => 'Merchant not found'], 404);
        }
    }

    public function payout(Affiliate $affiliate)
    {
        // Perform the payout for the affiliate
        $pendingOrders = $this->merchantService->payout($affiliate);

        return response()->json(['message' => 'Payout completed successfully', 'orders' => $pendingOrders]);
    }
    public function orderStats(Request $request, Merchant $merchant): JsonResponse
    {
        // Get parameters from the request (e.g., date range)
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Query orders based on the provided date range and merchant
        $orders = $merchant->orders();

        if ($startDate) {
            $orders->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $orders->whereDate('created_at', '<=', $endDate);
        }

        // Get order statistics
        $totalOrders = $orders->count();
        $totalCommission = $orders->sum('commission_owed');

        return response()->json([
            'total_orders' => $totalOrders,
            'total_commission' => $totalCommission,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }
}

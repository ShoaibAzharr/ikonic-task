<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MerchantController extends Controller
{
    public function __construct(
        MerchantService $merchantService
    ) {}

    /**
     * Useful order statistics for the merchant API.
     * 
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        // as per my view no need to call merchant service when we have relation to get the merchant
        $orders           = \auth()->user()->merchant->orders()->whereBetween('created_at', [$request->from, $request->to])->get();
        $withOutAffiliate = $orders->where('affiliate_id', NULL);

        return \response()->json([
            'count'            => $orders->count(),
            'commissions_owed' => $orders->sum('commission_owed') - $withOutAffiliate->sum('commission_owed'),
            'revenue'          => $orders->sum('subtotal'),
        ]);
    }
}

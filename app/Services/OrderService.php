<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {}

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
        \extract($data);
        $merchant = Merchant::firstWhere('domain', $merchant_domain);
        
        try {
            $this->affiliateService->register($merchant, $customer_email, $customer_name, 0.1);
            $affiliate = Affiliate::firstWhere('discount_code', $discount_code);
        } catch (\Exception $e) {
            $affiliate = User::firstWhere('email', $customer_email)->affiliate;
        }

        Order::firstOrCreate(['external_order_id' => $order_id],[
            'subtotal'        => $subtotal_price,
            'affiliate_id'    => $affiliate->id,
            'merchant_id'     => $merchant->id,
            'commission_owed' => $subtotal_price * $affiliate->commission_rate,
        ]);
    }
}

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
        \extract($data);
        $user = User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => $api_key,
            'type'     => User::TYPE_MERCHANT,
        ]);
        return $user->merchant()->create([
            'domain'       => $domain,
            'display_name' => $name,
        ]);
    }

    /**
     * Update the user
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, array $data)
    {
        \extract($data);
        $user->update([
            'name'     => $name,
            'email'    => $email,
            'password' => $api_key,
        ]);
        $user->merchant()->update([
            'domain'       => $domain,
            'display_name' => $name,
        ]);
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
        //using php 8 null-safe operator
        return User::firstWhere('email', $email)?->merchant;
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
        $orders = $affiliate->orders()->where('payout_status', Order::STATUS_UNPAID)->get();
        foreach ($orders as $order) {
            PayoutOrderJob::dispatch($order);
        }
    }
}

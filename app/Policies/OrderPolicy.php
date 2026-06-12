<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->buyer_id || $user->id === $order->seller_id;
    }

    public function ship(User $user, Order $order): bool
    {
        return $user->id === $order->seller_id && $order->status === 'paid';
    }

    public function confirm(User $user, Order $order): bool
    {
        return $user->id === $order->buyer_id && $order->status === 'delivered';
    }

    public function cancel(User $user, Order $order): bool
    {
        return $user->id === $order->buyer_id && $order->status === 'pending_payment';
    }
}

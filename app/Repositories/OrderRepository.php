<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Ramsey\Collection\Collection;

final class OrderRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Order());
    }

    public function findForUser(int $userId, int $orderId): ?Order
    {
        return Order::where('user_id', $userId)->where('id', $orderId)->first();
    }

    public function findByUser(int $userId)
    {
        return Order::where('user_id', $userId)->get();
    }

    public function findExpiredPendingOrders()
    {
        return Order::where('status', Order::STATUS_PENDING)
                ->where('expires_at', '<=', now())
                ->get();
    }

    public function addItem(array $data): OrderItem
    {
        return OrderItem::create($data);
    }
}

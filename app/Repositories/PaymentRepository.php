<?php

namespace App\Repositories;

use App\Models\Payment;
use Illuminate\Support\Facades\DB;

final class PaymentRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Payment());
    }

    public function findForUser(int $userId, int $paymentId): ?Payment
    {
        return Payment::whereHas('order', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->where('id', $paymentId)
        ->first();
    }

    public function findByIdempotency(string $key, int $orderId): ?Payment
    {
        return Payment::where('order_id', $orderId)->where('idempotency_key', $key)->where('status', Payment::STATUS_AUTHORIZED)->first();
    }

    public function voidActiveAuthorizations(int $orderId): void
    {
        Payment::where('order_id', $orderId)
            ->where('status', Payment::STATUS_AUTHORIZED)
            ->update(['status' => Payment::STATUS_VOIDED]);
    }

    public function getDailySettlement(string $date)
    {
        return Payment::join('orders', 'payments.order_id', '=', 'orders.id')
        ->select(
                'payments.status',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(orders.total_price) as total')
            )
            ->whereDate('payments.updated_at', $date)
            ->groupBy('payments.status')
            ->get()
            ->keyBy('status');
    }
}

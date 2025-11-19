<?php

namespace App\Repositories;

use App\Models\Refund;

final class RefundRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Refund());
    }

    public function getTotalRefund(int $paymentId): int
    {
        return Refund::where('payment_id', $paymentId)->where('status', '<>', Refund::STATUS_FAILED)->sum('amount');
    }
}

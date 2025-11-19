<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Refund;
use App\Repositories\RefundRepository;
use Exception;
use Illuminate\Http\Response;

final class RefundService
{
    private RefundRepository $refundRepository;

    public function __construct()
    {
        $this->refundRepository = new RefundRepository();
    }

    public function getTotalRefund(int $paymentId)
    {
        return $this->refundRepository->getTotalRefund($paymentId);
    }

    public function requestRefund(Payment $payment, int $amount, string $reason): Refund
    {
        $totalRefund = $this->getTotalRefund($payment->id);

        if ($payment->status != Payment::STATUS_CAPTURED) throw new Exception("Only captured payments can be refunded.", Response::HTTP_UNPROCESSABLE_ENTITY);

        if (((int)$totalRefund + (int)$amount) > $payment->order->total_price) throw new Exception("Total amount refund exceeded", Response::HTTP_UNPROCESSABLE_ENTITY);

        return $this->refundRepository->create([
            'payment_id' => $payment->id,
            'amount' => $amount,
            'reason' => $reason,
        ]);
    }
}

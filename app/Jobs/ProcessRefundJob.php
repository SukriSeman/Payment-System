<?php

namespace App\Jobs;

use App\Models\Refund;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\RefundService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Sleep;

use function Symfony\Component\Translation\t;

class ProcessRefundJob implements ShouldQueue
{
    use Queueable;

    private RefundService $refundService;
    private PaymentService $paymentService;

    /**
     * Create a new job instance.
     */
    public function __construct(private Refund $refund)
    {
        $this->refundService = new RefundService();
        $this->paymentService = new PaymentService();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->refund->update(['status' => Refund::STATUS_PROCESSING]);

        try {
            $order = $this->refund->payment->order;
            $totalRefund = $this->refundService->getTotalRefund($this->refund->payment_id);

            if($order->total_price == $totalRefund) {
                $this->paymentService->updateRefund($this->refund->payment);
            }

            $this->refund->update(['status' => Refund::STATUS_SUCCESS]);
        } catch (\Throwable $th) {
            Log::error(__CLASS__." ERROR: " . $th->getMessage());
            $this->refund->update(['status' => Refund::STATUS_FAILED]);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessRefundJob;
use App\Services\PaymentService;
use App\Services\RefundService;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    public function __construct(private RefundService $refundService) { }

    public function refund(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|integer|exists:payments,id',
            'amount' => 'required|integer',
            'reason' => 'string'
        ]);

        $paymentService = new PaymentService();
        $payment =  $paymentService->getPayment($request->get('payment_id'));

        $refund = $this->refundService->requestRefund($payment, $request->get('amount'), $request->get('reason', ''));

        dispatch(new ProcessRefundJob($refund));

        return response()->json([
            'status' => 'success',
            'data' => [
                'refund_id' => $refund->id
            ]
        ]);
    }
}

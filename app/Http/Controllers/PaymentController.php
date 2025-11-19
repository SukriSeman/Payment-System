<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService) { }

    public function initiate(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'idempotency_key' => 'required|string|max:100'
        ]);

        return Response()->json([
            'status' => 'success',
            'data' => [
                'payment_id' => $this->paymentService->createPayment($request->get('order_id'), $request->get('idempotency_key'))
            ]
        ]);
    }

    public function capture(int $id)
    {
        $payment = $this->paymentService->getPayment($id);
        $this->paymentService->changeStatus($payment, Payment::STATUS_CAPTURED);

        return Response()->json([
            'status' => 'success',
            'data' => []
        ]);
    }

    public function void(int $id)
    {
        $payment = $this->paymentService->getPayment($id);
        $this->paymentService->changeStatus($payment, Payment::STATUS_VOIDED);

        return Response()->json([
            'status' => 'success',
            'data' => []
        ]);
    }

}

<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final class PaymentService
{
    private PaymentRepository $paymentRepository;

    public function __construct()
    {
        $this->paymentRepository = new PaymentRepository();
    }

    public function getPayment($paymentId): Payment
    {
        $payment = $this->paymentRepository->findForUser(Auth::id(), $paymentId);

        if (!$payment) {
            throw new Exception('Payment not found.');
        }

        return $payment;
    }

    public function createPayment(int $orderId, string $key): int
    {
        $orderRepository = new OrderRepository();
        $order = $orderRepository->find($orderId);

        if (!$order) {
            throw new Exception('Order not found.', Response::HTTP_NOT_FOUND);
        }

        if ($order->status == Order::STATUS_CANCELLED) throw new Exception('Order already expired.', Response::HTTP_GONE);

        $payment = $this->paymentRepository->findByIdempotency($key, $orderId);

        if ($payment) {
            return $payment->id;
        }

        try {
            DB::beginTransaction();

            $payment = $this->paymentRepository->create([
                'order_id' => $orderId,
                'idempotency_key' => $key,
            ]);

            $order->update(['status' => Order::STATUS_CONFIRMED]);

            DB::commit();

            return $payment->id;

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function changeStatus(Payment $payment, string $status): void
    {
        if (!in_array($status, [Payment::STATUS_CAPTURED, Payment::STATUS_VOIDED])) throw new Exception('Unknown status given.');

        if ($payment->status != Payment::STATUS_AUTHORIZED && $payment->status != $status) {
            throw new Exception('Unable to update status.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($payment->status == Payment::STATUS_AUTHORIZED) {
            if ($payment->order->status == Order::STATUS_CANCELLED) throw new Exception('Order already expired.', Response::HTTP_GONE);

            try {
                DB::beginTransaction();

                $payment->update(['status' => $status]);

                $order = $payment->order();

                if ($status == Payment::STATUS_CAPTURED) {
                    $order->update([
                        'status' => Order::STATUS_FULFILLED
                    ]);
                }

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                throw $th;
            }
        }
    }

    public function updateRefund(Payment $payment): void
    {
        if ($payment->status != Payment::STATUS_CAPTURED) {
            throw new Exception('Unable to update status.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $this->paymentRepository->update($payment->id, ['status' => Payment::STATUS_REFUNDED]);
    }
}

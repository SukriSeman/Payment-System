<?php

namespace Tests\Unit;

use App\Jobs\ProcessRefundJob;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use App\Services\RefundService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class RefundServiceTest extends TestCase
{
    use RefreshDatabase;

    private RefundService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        Auth::login($user);

        $this->service = new RefundService();
    }

    public function test_creates_a_partial_refund_and_keeps_payment_captured()
    {
        $order = Order::factory()->create(['user_id' => Auth::id(), 'total_price' => 10000]);

        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'idempotency_key' => 'TEST-KEY-123',
            'status' => Payment::STATUS_CAPTURED,
        ]);

        $refund = $this->service->requestRefund($payment, 5000, fake()->text());

        Bus::dispatchSync(new ProcessRefundJob($refund));

        $this->assertDatabaseHas('refunds', [
            'id' => $refund->id,
            'payment_id' => $payment->id,
            'amount' => 5000,
            'status' => Refund::STATUS_SUCCESS,
        ]);

        $this->assertEquals(Payment::STATUS_CAPTURED, $payment->fresh()->status);
    }

    public function test_marks_payment_refunded_when_full_amount_refunded()
    {
        $order = Order::factory()->create(['user_id' => Auth::id(), 'total_price' => 10000]);

        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'idempotency_key' => 'TEST-KEY-456',
            'status' => Payment::STATUS_CAPTURED,
        ]);

        $refund = $this->service->requestRefund($payment, 10000, fake()->text());

        Bus::dispatchSync(new ProcessRefundJob($refund));

        $this->assertEquals(Payment::STATUS_REFUNDED, $payment->fresh()->status);
    }

    public function test_cannot_refund_more_than_payment_amount()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(Response::HTTP_UNPROCESSABLE_ENTITY);

        $order = Order::factory()->create(['user_id' => Auth::id(), 'total_price' => 5000]);

        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'idempotency_key' => 'TEST-KEY-789',
            'status' => Payment::STATUS_CAPTURED,
        ]);

        $this->service->requestRefund($payment, 10000, fake()->text());
    }

    public function cannot_refund_non_captured_payment()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(Response::HTTP_UNPROCESSABLE_ENTITY);

        $order = Order::factory()->create(['user_id' => Auth::id(), 'total_price' => 5000]);

        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'idempotency_key' => 'TEST-KEY-321',
            'status' => Payment::STATUS_AUTHORIZED,
        ]);

        $this->service->requestRefund($payment, 1000, fake()->text());
    }
}

<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\PaymentService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        Auth::login($user);

        $this->service = new PaymentService();
    }

    public function test_creates_a_new_payment_and_sets_authorized_status()
    {
        $order = Order::factory()->create(['user_id' => Auth::id()]);
        $key = 'KEY-TEST-123';

        $paymentId = $this->service->createPayment($order->id, $key);

        $this->assertDatabaseHas('payments', [
            'id' => $paymentId,
            'order_id' => $order->id,
            'idempotency_key' => $key,
            'status' => Payment::STATUS_AUTHORIZED,
        ]);
    }

    public function test_returns_existing_payment_for_same_idempotency_key()
    {
        $order = Order::factory()->create(['user_id' => Auth::id()]);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'idempotency_key' => 'SAME-KEY-123',
            'status' => Payment::STATUS_AUTHORIZED,
        ]);

        $found = $this->service->createPayment($order->id, 'SAME-KEY-123');

        $this->assertEquals($payment->id, $found);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_different_idempotency_key_creates_new_payment()
    {
        $order = Order::factory()->create(['user_id' => Auth::id()]);
        Payment::factory()->create([
            'order_id' => $order->id,
            'idempotency_key' => 'OLD-KEY-123',
            'status' => Payment::STATUS_AUTHORIZED,
        ]);

        $newKey = 'NEW-KEY-123';
        $newId = $this->service->createPayment($order->id, $newKey);

        $this->assertDatabaseCount('payments', 2);
        $this->assertDatabaseHas('payments', [
            'id' => $newId,
            'order_id' => $order->id,
            'idempotency_key' => $newKey,
            'status' => Payment::STATUS_AUTHORIZED,
        ]);
    }

    public function test_changes_status_from_authorized_to_captured()
    {
        $order = Order::factory()->create(['user_id' => Auth::id(), 'expires_at' => now()->addMinutes(30)]);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'idempotency_key' => fake()->text(10),
            'status' => Payment::STATUS_AUTHORIZED,
        ]);

        $this->service->changeStatus($payment, Payment::STATUS_CAPTURED);

        $this->assertEquals(Payment::STATUS_CAPTURED, $payment->fresh()->status);
    }

    public function test_changes_status_from_authorized_to_void()
    {
        $order = Order::factory()->create(['user_id' => Auth::id(), 'expires_at' => now()->addMinutes(30)]);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'idempotency_key' => fake()->text(10),
            'status' => Payment::STATUS_AUTHORIZED,
        ]);

        $this->service->changeStatus($payment, Payment::STATUS_VOIDED);

        $this->assertEquals(Payment::STATUS_VOIDED, $payment->fresh()->status);
    }

    public function test_capture_expired_payment()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(Response::HTTP_GONE);

        $order = Order::factory()->create(['user_id' => Auth::id(), 'expires_at' => now()->subMinutes(20), 'status' => Order::STATUS_CANCELLED]);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'idempotency_key' => fake()->text(10),
            'status' => Payment::STATUS_AUTHORIZED,
        ]);

        $this->service->changeStatus($payment, Payment::STATUS_CAPTURED);
    }

    public function test_capture_non_authorized_payment()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(Response::HTTP_UNPROCESSABLE_ENTITY);

        $payment = Payment::factory()->create([
            'idempotency_key' => fake()->text(10),
            'status' => Payment::STATUS_CAPTURED,
        ]);

        $this->service->changeStatus($payment, Payment::STATUS_VOIDED);
    }

    public function test_update_refund()
    {
        $payment = Payment::factory()->create([
            'idempotency_key' => fake()->text(10),
            'status' => Payment::STATUS_CAPTURED,
        ]);

        $this->service->updateRefund($payment);

        $this->assertEquals(Payment::STATUS_REFUNDED, $payment->fresh()->status);
    }

    public function test_cannot_update_refund()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(Response::HTTP_UNPROCESSABLE_ENTITY);

        $payment = Payment::factory()->create([
            'idempotency_key' => fake()->text(10),
            'status' => Payment::STATUS_VOIDED,
        ]);

        $this->service->updateRefund($payment);
    }
}

<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    private $token;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create(['password' => 'secret']);
        Auth::login($user);
        $userService = new UserService();

        $this->token = $userService->login($user->email, 'secret');

        Product::factory()->create();
    }

    public function test_product_api()
    {
        $productListResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)->getJson(route('product.list'));

        $productListResponse->assertStatus(Response::HTTP_OK);
    }

    public function test_create_order_and_authorize_payment_api()
    {
        $items = [
            ['id' => Product::first()->id, 'quantity' => 1]
        ];

        $orderResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson(route('order.create'),['items' => $items,]);

        $orderResponse->assertStatus(Response::HTTP_OK);
        $orderId = $orderResponse->json('data.order_id');


        $paymentResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson(route('payment.initiate'), [
                'order_id' => $orderId,
                'idempotency_key' => 'API-KEY-1'
            ]);
        $paymentResponse->assertStatus(Response::HTTP_OK);
        $paymentResponse->assertJsonStructure(['status', 'data' => ['payment_id']]);

        $this->assertDatabaseHas('payments', [
            'order_id' => $orderId,
            'idempotency_key' => 'API-KEY-1',
            'status' => Payment::STATUS_AUTHORIZED,
        ]);
    }

    public function test_get_order_api()
    {
        $order = Order::factory()->create(['user_id' => Auth::id()]);
        OrderItem::factory()->create(['order_id' => $order->id]);

        $productListResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)->getJson(route('order.show', $order->id));

        $productListResponse->assertStatus(Response::HTTP_OK);

        $productListResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)->getJson(route('order.list'));

        $productListResponse->assertStatus(Response::HTTP_OK);
    }

    public function test_changes_payment_status_to_capture()
    {
        $order = Order::factory()->create(['user_id' => Auth::id(), 'expires_at' => now()->addMinutes(20)]);

        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'idempotency_key' => 'KEY-ABC-123',
            'status' => Payment::STATUS_AUTHORIZED,
        ]);

        $resp = $this->withHeader('Authorization', 'Bearer ' . $this->token)->postJson(route('payment.capture',$payment->id));
        $resp->assertStatus(Response::HTTP_OK);

        $this->assertEquals(Payment::STATUS_CAPTURED, $payment->fresh()->status);
    }

    public function test_changes_payment_status_to_void()
    {
        $order = Order::factory()->create(['user_id' => Auth::id(), 'expires_at' => now()->addMinutes(20)]);

        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'idempotency_key' => 'KEY-ABC-456',
            'status' => Payment::STATUS_AUTHORIZED,
        ]);

        $resp = $this->withHeader('Authorization', 'Bearer ' . $this->token)->postJson(route('payment.void',$payment->id));
        $resp->assertStatus(Response::HTTP_OK);

        $this->assertEquals(Payment::STATUS_VOIDED, $payment->fresh()->status);
    }

    public function test_request_refund_and_it_creates_refund_record()
    {
        // Make queue run immediately
        config(['queue.default' => 'sync']);

        $order = Order::factory()->create(['user_id' => Auth::id(), 'total_price' => 9900]);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'idempotency_key' => 'KEY-ABC-789',
            'status' => Payment::STATUS_CAPTURED,
        ]);

        $resp = $this->withHeader('Authorization', 'Bearer ' . $this->token)->postJson(route('payment.refund'), [
            'payment_id' => $payment->id,
            'amount' => 9900,
            'reason' => 'Customer request',
        ]);

        $resp->assertStatus(Response::HTTP_OK);
        $this->assertDatabaseHas('refunds', [
            'payment_id' => $payment->id,
            'amount' => 9900,
        ]);

        $this->assertEquals(Payment::STATUS_REFUNDED, $payment->fresh()->status);
    }
}

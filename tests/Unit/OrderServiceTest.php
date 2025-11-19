<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

// use PHPUnit\Framework\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        Auth::login($user);

        $this->service = new OrderService();
    }

    public function test_creates_order_with_pending_status_and_expires_at()
    {
        $productList = [];
        $totalPrice = 0;
        $totalItem = 3;

        for ($i=0; $i < $totalItem; $i++) {
            $product = Product::factory()->create();

            $quantity = fake()->randomDigit();

            $productList[] = [
                'id' => $product->id,
                'quantity' => $quantity,
            ];

            $totalPrice += ($product->price * $quantity);
        }


        $orderId = $this->service->createOrder($productList);

        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'total_price' => $totalPrice
        ]);

        $order = $this->service->getOrder($orderId);

        $this->assertEquals(Order::STATUS_PENDING, $order->status);
        $this->assertNotNull($order->expires_at);
        $this->assertCount($totalItem, $order->items);
    }
}

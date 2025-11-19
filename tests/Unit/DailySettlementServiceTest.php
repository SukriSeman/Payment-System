<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class DailySettlementServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReportService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        Auth::login($user);

        $this->service = new ReportService();
    }

    public function test_returns_counts_and_totals_grouped_by_status_for_given_date()
    {
        $order1 = Order::factory()->create(['total_price' => 7000]);
        $order2 = Order::factory()->create(['total_price' => 3000]);

        Payment::factory()->create([
            'order_id' => $order1->id,
            'status' => Payment::STATUS_CAPTURED,
            'idempotency_key' => fake()->text(10),
            'updated_at' => now(),
        ]);

        Payment::factory()->create([
            'order_id' => $order2->id,
            'status' => Payment::STATUS_REFUNDED,
            'idempotency_key' => fake()->text(10),
            'updated_at' => now(),
        ]);

        $report = $this->service->dailySettlement(now()->toDateString());

        $this->assertArrayHasKey(Payment::STATUS_CAPTURED, $report);
        $this->assertArrayHasKey(Payment::STATUS_REFUNDED, $report);

        $this->assertEquals(7000, $report[Payment::STATUS_CAPTURED]['total']);
        $this->assertEquals(3000, $report[Payment::STATUS_REFUNDED]['total']);
    }
}

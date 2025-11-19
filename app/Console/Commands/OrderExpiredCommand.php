<?php

namespace App\Console\Commands;

use App\Services\OrderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use function Symfony\Component\Clock\now;

class OrderExpiredCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To tag CANCELLED to expired order.';

    /**
     * Execute the console command.
     */
    public function handle(OrderService $orderService)
    {
        try {
            $orderService->expirePendingOrders();
        } catch (\Throwable $th) {
            Log::error(__CLASS__." ERROR : " . $th->getMessage());
        }
    }
}

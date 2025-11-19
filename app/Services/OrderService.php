<?php

namespace App\Services;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\ProductRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

final class OrderService
{
    private OrderRepository $orderRepository;

    public function __construct()
    {
        $this->orderRepository = new OrderRepository;
    }

    public function getOrder(int $orderId)
    {
        $order = $this->orderRepository->findForUser(Auth::id(), $orderId);

        if (!$order) {
            throw new Exception('Order not found.', Response::HTTP_NOT_FOUND);
        }

        return $order;
    }

    public function getUserOrder(?int $userId = null)
    {
        if (empty($userId)) $userId = Auth::id();

        return OrderResource::collection($this->orderRepository->findByUser($userId));
    }

    public function createOrder($productList) : int
    {
        $productRepository = new ProductRepository();

        if (count($productList) == 0) {
            throw new Exception("Please add at least 1 product.", Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            /** @var Order $order */
            $order = $this->orderRepository->create([
                'user_id' => Auth::id(),
                'expires_at' => Carbon::now()->addMinutes(config('systems.payment_expired_duration'))
            ]);

            $totalPrice = 0;

            foreach ($productList as $product) {
                /** @var Product $_product */
                $_product = $productRepository->find($product['id']);

                if (!$_product) {
                    DB::rollBack();
                    throw new Exception('Product no found.', Response::HTTP_NOT_FOUND);
                }

                $totalPrice += ($_product->price * $product['quantity']);

                $this->addItem($order->id, $product['quantity'], $_product);
            }

            $order->update([
                'total_price' => $totalPrice
            ]);

            DB::commit();

            return $order->id;

        } catch (Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function expirePendingOrders(): void
    {
        $paymentRepository = new PaymentRepository();
        $orders = $this->orderRepository->findExpiredPendingOrders();

        foreach ($orders as $order) {

            $this->orderRepository->update($order->id, [
                'status' => Order::STATUS_CANCELLED
            ]);

            $paymentRepository->voidActiveAuthorizations($order->id);
        }
    }

    private function addItem(int $orderId, int $quantity, Product $product)
    {
        return  $this->orderRepository->addItem([
            'order_id' => $orderId,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $product->price,
            'total_price' => ($product->price * $quantity),
        ]);
    }
}

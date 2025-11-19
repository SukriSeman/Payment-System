<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) { }

    public function list(Request $request)
    {
        $request->validate([
            'userId' => 'integer',
        ]);

        $orderList = $this->orderService->getUserOrder($request->get('userId'));

        return Response()->json([
            'status' => 'success',
            'data' => OrderResource::collection($orderList)
        ]);
    }

    public function show(int $id)
    {
        $order = $this->orderService->getOrder($id);

        return Response()->json([
            'status' => 'success',
            'data' => new OrderResource($order)
        ]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $orderId = $this->orderService->createOrder($request->get('items'));

        if (empty($orderId)) {

            return Response()->json([
                'status' => 'error',
                'message' => 'Something went wrong, please try again.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);

        } else {

            return Response()->json([
                'status' => 'success',
                'data' => [
                    'order_id' => $orderId
                ]
            ]);

        }

    }
}

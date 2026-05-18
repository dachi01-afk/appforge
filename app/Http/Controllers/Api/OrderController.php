<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OrderRequest;
use App\Http\Resources\Api\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of the user's orders.
     */
    public function index(Request $request)
    {
        $orders = $this->orderService->getOrdersForUser($request->user());

        return OrderResource::collection($orders);
    }

    /**
     * Store a newly created order in storage.
     */
    public function store(OrderRequest $request)
    {
        $order = $this->orderService->createOrder($request->validated(), $request->user());

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized.');
        }

        $order->load(['detail', 'files', 'statusHistories.changer']);

        return new OrderResource($order);
    }

    /**
     * Update the specified order in storage.
     */
    public function update(OrderRequest $request, Order $order)
    {
        // Double check authorization is handled by OrderRequest::authorize
        try {
            $updatedOrder = $this->orderService->updateOrder($order, $request->validated(), $request->user());
            return new OrderResource($updatedOrder);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }
}

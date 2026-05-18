<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MessageRequest;
use App\Http\Resources\Api\MessageResource;
use App\Models\Order;
use App\Services\MessageService;
use Illuminate\Http\Request;

class OrderMessageController extends Controller
{
    protected $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    /**
     * Display a listing of messages for a specific order.
     */
    public function index(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized.');
        }

        // Fetch messages
        $messages = $this->messageService->getMessagesForOrder($order);

        // Mark all incoming messages in this order as read for the current user
        $this->messageService->markAsRead($order, $request->user());

        return MessageResource::collection($messages);
    }

    /**
     * Store a newly created message inside an order.
     */
    public function store(MessageRequest $request, Order $order)
    {
        $message = $this->messageService->sendMessage($order, $request->user(), $request->validated());

        return (new MessageResource($message))
            ->response()
            ->setStatusCode(201);
    }
}

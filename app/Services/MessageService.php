<?php

namespace App\Services;

use App\Models\Message;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MessageService
{
    /**
     * Get all messages for a specific order.
     */
    public function getMessagesForOrder(Order $order)
    {
        return $order->messages()
            ->with('sender')
            ->oldest()
            ->get();
    }

    /**
     * Send a new message inside an order.
     */
    public function sendMessage(Order $order, User $sender, array $data)
    {
        return DB::transaction(function () use ($order, $sender, $data) {
            $attachmentPath = null;

            if (isset($data['attachment']) && $data['attachment']->isValid()) {
                $attachmentPath = $data['attachment']->store('attachments', 'public');
            }

            $message = $order->messages()->create([
                'sender_id' => $sender->id,
                'message' => $data['message'],
                'attachment' => $attachmentPath,
                'is_read' => false,
            ]);

            return $message->load('sender');
        });
    }

    /**
     * Mark incoming messages as read for a specific user.
     */
    public function markAsRead(Order $order, User $user)
    {
        return $order->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }
}

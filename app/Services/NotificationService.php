<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserNotification;

class NotificationService
{
    /**
     * Send a notification to a specific user.
     */
    public function send(User|int $user, string $title, string $message): UserNotification
    {
        $userId = $user instanceof User ? $user->id : $user;

        return UserNotification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'is_read' => false,
        ]);
    }

    /**
     * Get all notifications for a specific user.
     */
    public function getNotificationsForUser(User|int $user)
    {
        $userId = $user instanceof User ? $user->id : $user;

        return UserNotification::where('user_id', $userId)
            ->latest()
            ->get();
    }

    /**
     * MarkAllAsRead marks all notifications as read for a specific user.
     */
    public function markAllAsRead(User|int $user): void
    {
        $userId = $user instanceof User ? $user->id : $user;

        UserNotification::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }
}

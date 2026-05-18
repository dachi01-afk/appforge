<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'attachment_url' => $this->attachment ? asset('storage/' . $this->attachment) : null,
            'is_read' => $this->is_read,
            'created_at' => $this->created_at?->toIso8601String(),
            'sender' => [
                'id' => $this->sender->id,
                'name' => $this->sender->name,
                'avatar_url' => $this->sender->avatar ? asset('storage/' . $this->sender->avatar) : null,
                'role' => $this->sender->role,
            ],
        ];
    }
}

<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'order_code' => $this->order_code,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'app_type' => $this->app_type,
            'platform' => $this->platform,
            'budget' => $this->budget,
            'estimated_price' => $this->estimated_price,
            'deadline' => $this->deadline?->toDateString(),
            'priority' => $this->priority,
            'status' => $this->status,
            'progress' => $this->progress,
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'detail' => new OrderDetailResource($this->whenLoaded('detail')),
            'files' => OrderFileResource::collection($this->whenLoaded('files')),
            'status_histories' => $this->when($this->relationLoaded('statusHistories'), function () {
                return $this->statusHistories->map(function ($history) {
                    return [
                        'old_status' => $history->old_status,
                        'new_status' => $history->new_status,
                        'note' => $history->note,
                        'changed_at' => $history->created_at?->toIso8601String(),
                        'changed_by' => $history->changer ? [
                            'name' => $history->changer->name,
                            'role' => $history->changer->role,
                        ] : null,
                    ];
                });
            }),
        ];
    }
}

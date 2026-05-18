<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'feature_list' => $this->feature_list,
            'design_preference' => $this->design_preference,
            'reference_app' => $this->reference_app,
            'target_user' => $this->target_user,
            'business_flow' => $this->business_flow,
            'additional_notes' => $this->additional_notes,
        ];
    }
}

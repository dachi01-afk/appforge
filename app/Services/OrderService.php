<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrderService
{
    /**
     * Get paginated orders for a specific user.
     */
    public function getOrdersForUser(User $user, int $perPage = 15)
    {
        return Order::where('user_id', $user->id)
            ->with(['detail', 'files'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get a specific order by order_code for a specific user.
     */
    public function getOrderByCode(string $orderCode, User $user)
    {
        return Order::where('order_code', $orderCode)
            ->where('user_id', $user->id)
            ->with(['detail', 'files', 'statusHistories.changer'])
            ->firstOrFail();
    }

    /**
     * Create a new order with details, files, and status history.
     */
    public function createOrder(array $data, User $user)
    {
        return DB::transaction(function () use ($data, $user) {
            // 1. Generate unique order code
            $orderCode = 'ORD-' . strtoupper(Str::random(8));
            while (Order::where('order_code', $orderCode)->exists()) {
                $orderCode = 'ORD-' . strtoupper(Str::random(8));
            }

            // 2. Generate unique slug
            $slug = Str::slug($data['title']);
            $originalSlug = $slug;
            $count = 1;
            while (Order::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }

            // 3. Create Order
            $order = Order::create([
                'order_code' => $orderCode,
                'user_id' => $user->id,
                'title' => $data['title'],
                'slug' => $slug,
                'description' => $data['description'],
                'app_type' => $data['app_type'],
                'platform' => $data['platform'],
                'budget' => $data['budget'],
                'deadline' => $data['deadline'] ?? null,
                'priority' => $data['priority'],
                'status' => 'pending',
                'progress' => 0,
            ]);

            // 4. Create Order Detail
            $order->detail()->create([
                'feature_list' => $data['feature_list'],
                'design_preference' => $data['design_preference'],
                'reference_app' => $data['reference_app'] ?? null,
                'target_user' => $data['target_user'] ?? null,
                'business_flow' => $data['business_flow'] ?? null,
                'additional_notes' => $data['additional_notes'] ?? null,
            ]);

            // 5. Handle File Uploads
            if (isset($data['files'])) {
                foreach ($data['files'] as $file) {
                    $path = $file->store('orders', 'public');
                    $order->files()->create([
                        'uploaded_by' => $user->id,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }

            // 6. Record Status History
            $order->statusHistories()->create([
                'old_status' => null,
                'new_status' => 'pending',
                'changed_by' => $user->id,
                'note' => 'Order created successfully.',
            ]);

            return $order->load(['detail', 'files', 'statusHistories.changer']);
        });
    }

    /**
     * Update an existing order (only allowed if status is pending).
     */
    public function updateOrder(Order $order, array $data, User $user)
    {
        if ($order->status !== 'pending') {
            throw new \Exception('Only pending orders can be updated.');
        }

        return DB::transaction(function () use ($order, $data, $user) {
            // Update slug if title changed
            if (isset($data['title']) && $data['title'] !== $order->title) {
                $slug = Str::slug($data['title']);
                $originalSlug = $slug;
                $count = 1;
                while (Order::where('slug', $slug)->where('id', '!=', $order->id)->exists()) {
                    $slug = $originalSlug . '-' . $count++;
                }
                $order->slug = $slug;
            }

            // Update main order
            $order->update(array_filter([
                'title' => $data['title'] ?? null,
                'description' => $data['description'] ?? null,
                'app_type' => $data['app_type'] ?? null,
                'platform' => $data['platform'] ?? null,
                'budget' => $data['budget'] ?? null,
                'deadline' => $data['deadline'] ?? null,
                'priority' => $data['priority'] ?? null,
            ]));

            // Update details
            $detailData = array_filter([
                'feature_list' => $data['feature_list'] ?? null,
                'design_preference' => $data['design_preference'] ?? null,
                'reference_app' => $data['reference_app'] ?? null,
                'target_user' => $data['target_user'] ?? null,
                'business_flow' => $data['business_flow'] ?? null,
                'additional_notes' => $data['additional_notes'] ?? null,
            ]);

            if (!empty($detailData)) {
                $order->detail()->update($detailData);
            }

            // Handle new file uploads
            if (isset($data['files'])) {
                foreach ($data['files'] as $file) {
                    $path = $file->store('orders', 'public');
                    $order->files()->create([
                        'uploaded_by' => $user->id,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }

            return $order->load(['detail', 'files', 'statusHistories.changer']);
        });
    }
}

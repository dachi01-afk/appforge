<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Order;
use App\Models\Message;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MessageApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_client_can_send_and_retrieve_messages()
    {
        // 1. Get seeded client
        $client = User::where('role', 'client')->first();
        if (!$client) {
            $client = User::factory()->create([
                'name' => 'Jimi Client',
                'email' => 'client.test@appforge.com',
                'password' => bcrypt('password'),
                'role' => 'client',
            ]);
        }

        // 2. Get or create an order for the client
        $order = Order::where('user_id', $client->id)->first();
        if (!$order) {
            $order = Order::create([
                'order_code' => 'ORD-TESTCHAT',
                'user_id' => $client->id,
                'title' => 'Test Application',
                'slug' => 'test-application-chat',
                'description' => 'A test description for chat testing',
                'app_type' => 'web',
                'platform' => 'browser',
                'budget' => 2000,
                'priority' => 'low',
                'status' => 'pending',
                'progress' => 0,
            ]);
        }

        $orderCode = $order->order_code;

        // Clear existing messages for this order inside transaction to make assertion predictable
        Message::where('order_id', $order->id)->delete();

        // 3. Test sending a message via POST /api/orders/{order_code}/messages
        $payload = [
            'message' => 'Halo Admin, ini pesan test dari feature test.',
        ];

        $response = $this->actingAs($client, 'sanctum')
            ->postJson("/api/orders/{$orderCode}/messages", $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'message',
                    'attachment_url',
                    'is_read',
                    'created_at',
                    'sender' => [
                        'id',
                        'name',
                        'avatar_url',
                        'role',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('messages', [
            'order_id' => $order->id,
            'sender_id' => $client->id,
            'message' => 'Halo Admin, ini pesan test dari feature test.',
        ]);

        // 4. Test fetching messages via GET /api/orders/{order_code}/messages
        $getResponse = $this->actingAs($client, 'sanctum')
            ->getJson("/api/orders/{$orderCode}/messages");

        $getResponse->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.message', 'Halo Admin, ini pesan test dari feature test.');
    }
}

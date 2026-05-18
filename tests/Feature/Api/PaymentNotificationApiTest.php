<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentNotificationApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_payment_and_notification_flow()
    {
        Storage::fake('public');

        // 1. Get seeded client and admin
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
                'order_code' => 'ORD-TESTPAY',
                'user_id' => $client->id,
                'title' => 'Test Payment Application',
                'slug' => 'test-payment-application',
                'description' => 'A test description for payment testing',
                'app_type' => 'web',
                'platform' => 'browser',
                'budget' => 2000000,
                'estimated_price' => 2500000,
                'priority' => 'low',
                'status' => 'pending',
                'progress' => 0,
            ]);
        } else {
            $order->update(['estimated_price' => 2500000]);
        }

        // 3. Create a pending payment
        $payment = Payment::create([
            'order_id' => $order->id,
            'invoice_number' => 'INV-TEST-999',
            'amount' => 2500000,
            'status' => 'pending',
        ]);

        // 4. Test client fetching the payment details
        $response = $this->actingAs($client, 'sanctum')
            ->getJson("/api/payments/{$payment->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'order_id',
                    'invoice_number',
                    'amount',
                    'payment_method',
                    'payment_proof',
                    'payment_proof_url',
                    'status',
                    'paid_at',
                    'created_at',
                ],
            ])
            ->assertJsonPath('data.invoice_number', 'INV-TEST-999');

        // 5. Test client uploading payment proof
        $file = UploadedFile::fake()->image('proof.jpg');

        $uploadResponse = $this->actingAs($client, 'sanctum')
            ->postJson("/api/payments/{$payment->id}/proof", [
                'payment_method' => 'Transfer Bank BCA',
                'payment_proof' => $file,
            ]);

        $uploadResponse->assertStatus(200)
            ->assertJsonPath('data.payment_method', 'Transfer Bank BCA')
            ->assertJsonPath('data.status', 'pending');

        // Verify the file was stored on public storage
        $storedPath = $uploadResponse->json('data.payment_proof');
        Storage::disk('public')->assertExists($storedPath);

        // Verify database notification was created for client
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $client->id,
            'title' => 'Payment Proof Submitted',
        ]);

        // 6. Test client fetching notifications list
        $notifResponse = $this->actingAs($client, 'sanctum')
            ->getJson('/api/notifications');

        $notifResponse->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'title',
                        'message',
                        'is_read',
                        'created_at',
                    ]
                ]
            ]);

        // 7. Test client marking all notifications as read
        $readResponse = $this->actingAs($client, 'sanctum')
            ->postJson('/api/notifications/read-all');

        $readResponse->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        // Verify database updated notification status
        $this->assertDatabaseMissing('user_notifications', [
            'user_id' => $client->id,
            'is_read' => false,
        ]);
    }
}

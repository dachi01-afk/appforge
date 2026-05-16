<?php

namespace Database\Seeders;

use App\Models\Message;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderFile;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        */

        $admin = User::create([
            'name' => 'Admin AppForge',
            'email' => 'admin@appforge.test',
            'password' => Hash::make('password'),
            'phone' => '081234567890',
            'role' => 'admin',
        ]);

        /*
        |--------------------------------------------------------------------------
        | CLIENT
        |--------------------------------------------------------------------------
        */

        $client = User::create([
            'name' => 'Jimi Firgo',
            'email' => 'client@appforge.test',
            'password' => Hash::make('password'),
            'phone' => '089876543210',
            'role' => 'client',
        ]);

        /*
        |--------------------------------------------------------------------------
        | ORDER
        |--------------------------------------------------------------------------
        */

        $order = Order::create([
            'order_code' => 'ORD-001',
            'user_id' => $client->id,
            'title' => 'Aplikasi Laundry',
            'slug' => 'aplikasi-laundry',
            'description' => 'Aplikasi laundry berbasis mobile dan admin dashboard.',
            'app_type' => 'mobile',
            'platform' => 'android',
            'budget' => 5000000,
            'estimated_price' => 6500000,
            'deadline' => now()->addDays(30),
            'priority' => 'high',
            'status' => 'in_progress',
            'progress' => 40,
            'started_at' => now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | ORDER DETAIL
        |--------------------------------------------------------------------------
        */

        OrderDetail::create([
            'order_id' => $order->id,
            'feature_list' => [
                'Login',
                'Register',
                'Order Laundry',
                'Payment Gateway',
                'Tracking Order',
            ],
            'design_preference' => 'Modern clean UI',
            'reference_app' => 'https://example.com',
            'target_user' => 'Pemilik laundry',
            'business_flow' => 'Customer order -> admin proses -> driver antar',
            'additional_notes' => 'Harus support dark mode',
        ]);

        /*
        |--------------------------------------------------------------------------
        | ORDER FILE
        |--------------------------------------------------------------------------
        */

        OrderFile::create([
            'order_id' => $order->id,
            'uploaded_by' => $client->id,
            'file_name' => 'mockup-ui.pdf',
            'file_path' => 'orders/mockup-ui.pdf',
            'file_type' => 'application/pdf',
            'file_size' => 2048,
        ]);

        /*
        |--------------------------------------------------------------------------
        | ORDER STATUS HISTORY
        |--------------------------------------------------------------------------
        */

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'old_status' => 'pending',
            'new_status' => 'in_progress',
            'changed_by' => $admin->id,
            'note' => 'Order mulai dikerjakan',
        ]);

        /*
        |--------------------------------------------------------------------------
        | MESSAGE
        |--------------------------------------------------------------------------
        */

        Message::create([
            'order_id' => $order->id,
            'sender_id' => $client->id,
            'message' => 'Halo admin, saya ingin revisi warna aplikasi.',
            'is_read' => false,
        ]);

        /*
        |--------------------------------------------------------------------------
        | PAYMENT
        |--------------------------------------------------------------------------
        */

        Payment::create([
            'order_id' => $order->id,
            'invoice_number' => 'INV-001',
            'amount' => 3000000,
            'payment_method' => 'Bank Transfer',
            'payment_proof' => 'payments/payment-proof.jpg',
            'paid_at' => now(),
            'status' => 'paid',
        ]);

        /*
        |--------------------------------------------------------------------------
        | NOTIFICATION
        |--------------------------------------------------------------------------
        */

        UserNotification::create([
            'user_id' => $client->id,
            'title' => 'Order Diproses',
            'message' => 'Order anda sedang dikerjakan.',
            'is_read' => false,
        ]);
    }
}

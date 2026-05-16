<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code')->unique();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title');
            $table->string('slug')->nullable();

            $table->longText('description');

            $table->enum('app_type', [
                'web',
                'mobile',
                'desktop',
            ]);

            $table->string('platform');

            $table->decimal('budget', 15, 2);

            $table->decimal('estimated_price', 15, 2)
                ->nullable();

            $table->date('deadline')
                ->nullable();

            $table->enum('priority', [
                'low',
                'medium',
                'high',
            ])->default('medium');

            $table->enum('status', [
                'pending',
                'review',
                'approved',
                'rejected',
                'in_progress',
                'revision',
                'done',
                'cancelled',
            ])->default('pending');

            $table->integer('progress')
                ->default(0);

            $table->timestamp('started_at')
                ->nullable();

            $table->timestamp('completed_at')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

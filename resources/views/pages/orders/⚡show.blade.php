<?php

use Livewire\Component;
use App\Models\Order;
use App\Services\NotificationService;

new class extends Component
{

    public Order $order;


    public bool $showStatusModal = false;

    public string $status = '';

    public int $progress = 0;

    public string $note = '';

    public string $estimated_price = '';

    public function mount(Order $order): void
    {
        $this->order = $order->load([
            'user',
            'detail',
            'files',
            'statusHistories.changer',
            'messages.sender',
            'payments',
        ]);

        $this->status = $order->status;

        $this->progress = $order->progress;

        $this->estimated_price = $order->estimated_price ? (string) (int) $order->estimated_price : '';
    }

    public function updateStatus(): void
    {
        $this->validate([
            'status' => 'required',
            'progress' => 'required|integer|min:0|max:100',
            'note' => 'nullable|string|max:1000',
            'estimated_price' => 'nullable|numeric|min:0',
        ]);

        $oldStatus = $this->order->status;

        $this->order->update([
            'status' => $this->status,
            'progress' => $this->progress,
            'estimated_price' => $this->estimated_price ?: null,

            'started_at' => $this->status === 'in_progress'
                ? now()
                : $this->order->started_at,

            'completed_at' => $this->status === 'done'
                ? now()
                : null,
        ]);

        $this->order->statusHistories()->create([
            'old_status' => $oldStatus,
            'new_status' => $this->status,
            'changed_by' => auth()->id(),
            'note' => $this->note,
        ]);

        if ($oldStatus !== $this->status) {
            app(NotificationService::class)->send(
                $this->order->user,
                'Order Status Updated',
                "Your order '{$this->order->title}' status has been updated to " . str_replace('_', ' ', $this->status)
            );
        }

        $this->order->refresh();

        $this->showStatusModal = false;

        $this->note = '';

        $this->dispatch(
            'notify',
            type: 'success',
            message: 'Order status updated successfully.'
        );
    }

    public function generateInvoice(): void
    {
        if ($this->order->payments()->exists()) {
            $this->dispatch('notify', type: 'error', message: 'Invoice already exists.');
            return;
        }

        if (!$this->order->estimated_price) {
            $this->dispatch('notify', type: 'error', message: 'Set Estimated Price first.');
            return;
        }

        $invoiceNumber = 'INV-' . strtoupper(uniqid());

        $this->order->payments()->create([
            'invoice_number' => $invoiceNumber,
            'amount' => $this->order->estimated_price,
            'status' => 'pending',
        ]);

        app(NotificationService::class)->send(
            $this->order->user,
            'Invoice Generated',
            "An invoice '{$invoiceNumber}' has been generated for order '{$this->order->title}' with amount Rp " . number_format($this->order->estimated_price)
        );

        $this->order->refresh();

        $this->dispatch('notify', type: 'success', message: 'Invoice generated successfully.');
    }
};
?>

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        <div>

            <div class="flex items-center gap-3 mb-2">

                <flux:badge color="sky">
                    {{ $order->order_code }}
                </flux:badge>

                <flux:badge
                    :color="match($order->status) {
                        'pending' => 'zinc',
                        'review' => 'yellow',
                        'approved' => 'sky',
                        'in_progress' => 'blue',
                        'revision' => 'orange',
                        'done' => 'green',
                        'rejected' => 'red',
                        default => 'zinc'
                    }"
                >
                    {{ str($order->status)->replace('_', ' ')->title() }}
                </flux:badge>

            </div>

            <h1 class="text-3xl font-bold tracking-tight">
                {{ $order->title }}
            </h1>

            <p class="mt-2 text-zinc-500">
                Client:
                <span class="font-medium text-zinc-700 dark:text-zinc-200">
                    {{ $order->user->name }}
                </span>
            </p>

        </div>

        <div class="flex flex-wrap gap-3">

            <flux:button
                variant="ghost"
                :href="route('orders.index')"
                wire:navigate
                icon="arrow-left"
            >
                Back
            </flux:button>

            <flux:button
                variant="primary"
                icon="pencil-square"
                wire:click="$set('showStatusModal', true)"
            >
                Update Status
            </flux:button>

            <flux:button
                variant="subtle"
                icon="chat-bubble-left-right"
                :href="route('inbox.index', ['order' => $order->id])"
                wire:navigate
            >
                Chat Client
            </flux:button>

        </div>

    </div>

    {{-- Stats --}}
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">

        <div class="p-5 bg-white border shadow-sm rounded-2xl dark:bg-zinc-900 dark:border-zinc-800">
            <p class="mb-1 text-sm text-zinc-500">
                Budget
            </p>

            <h3 class="text-2xl font-bold">
                Rp {{ number_format($order->budget) }}
            </h3>
        </div>

        <div class="p-5 bg-white border shadow-sm rounded-2xl dark:bg-zinc-900 dark:border-zinc-800">
            <p class="mb-1 text-sm text-zinc-500">
                Estimated Price
            </p>

            <h3 class="text-2xl font-bold">
                Rp {{ number_format($order->estimated_price ?? 0) }}
            </h3>
        </div>

        <div class="p-5 bg-white border shadow-sm rounded-2xl dark:bg-zinc-900 dark:border-zinc-800">
            <p class="mb-1 text-sm text-zinc-500">
                Deadline
            </p>

            <h3 class="text-xl font-bold">
                {{ $order->deadline?->format('d M Y') ?? '-' }}
            </h3>
        </div>

        <div class="p-5 bg-white border shadow-sm rounded-2xl dark:bg-zinc-900 dark:border-zinc-800">
            <p class="mb-3 text-sm text-zinc-500">
                Progress
            </p>

            <flux:progress :value="$order->progress" />

            <p class="mt-2 text-sm font-medium">
                {{ $order->progress }}%
            </p>
        </div>

    </div>

    {{-- Main Content --}}
    <div class="grid gap-6 xl:grid-cols-3">

        {{-- Left --}}
        <div class="space-y-6 xl:col-span-2">

            {{-- Description --}}
            <div class="p-6 bg-white border shadow-sm rounded-2xl dark:bg-zinc-900 dark:border-zinc-800">

                <h2 class="mb-4 text-lg font-semibold">
                    Project Description
                </h2>

                <div class="prose max-w-none dark:prose-invert">
                    {{ $order->description }}
                </div>

            </div>

            {{-- Project Detail --}}
            <div class="p-6 bg-white border shadow-sm rounded-2xl dark:bg-zinc-900 dark:border-zinc-800">

                <h2 class="mb-5 text-lg font-semibold">
                    Project Detail
                </h2>

                <div class="grid gap-5 md:grid-cols-2">

                    <div>
                        <p class="mb-1 text-sm text-zinc-500">
                            App Type
                        </p>

                        <p class="font-medium">
                            {{ $order->app_type }}
                        </p>
                    </div>

                    <div>
                        <p class="mb-1 text-sm text-zinc-500">
                            Platform
                        </p>

                        <p class="font-medium">
                            {{ $order->platform }}
                        </p>
                    </div>

                    <div>
                        <p class="mb-1 text-sm text-zinc-500">
                            Priority
                        </p>

                        <p class="font-medium">
                            {{ ucfirst($order->priority) }}
                        </p>
                    </div>

                    <div>
                        <p class="mb-1 text-sm text-zinc-500">
                            Created At
                        </p>

                        <p class="font-medium">
                            {{ $order->created_at->format('d M Y H:i') }}
                        </p>
                    </div>

                    <div>
                        <p class="mb-1 text-sm text-zinc-500">
                            Started At
                        </p>

                        <p class="font-medium">
                            {{ $order->started_at?->format('d M Y H:i') ?? '-' }}
                        </p>
                    </div>

                    <div>
                    <p class="mb-1 text-sm text-zinc-500">
                        Completed At
                    </p>

                    <p class="font-medium">
                        {{ $order->completed_at?->format('d M Y H:i') ?? '-' }}
                    </p>
                </div>

                </div>

            </div>

            {{-- Features --}}
            <div class="p-6 bg-white border shadow-sm rounded-2xl dark:bg-zinc-900 dark:border-zinc-800">

                <h2 class="mb-5 text-lg font-semibold">
                    Requested Features
                </h2>

                <div class="flex flex-wrap gap-2">

                    @foreach($order->detail?->feature_list ?? [] as $feature)

                        <flux:badge color="sky">
                            {{ $feature }}
                        </flux:badge>

                    @endforeach

                </div>

            </div>

              <div class="p-6 bg-white border shadow-sm rounded-2xl dark:bg-zinc-900 dark:border-zinc-800">
                <h2 class="mb-6 text-lg font-semibold">
                    Client Requirement
                </h2>
                <div class="space-y-5">

                    <div>
                        <p class="mb-1 text-sm text-zinc-500">
                            Design Preference
                        </p>

                        <p>
                            {{ $order->detail?->design_preference ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="mb-1 text-sm text-zinc-500">
                            Reference App
                        </p>

                        <p>
                            {{ $order->detail?->reference_app ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="mb-1 text-sm text-zinc-500">
                            Target User
                        </p>

                        <p>
                            {{ $order->detail?->target_user ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="mb-1 text-sm text-zinc-500">
                            Business Flow
                        </p>

                        <p>
                            {{ $order->detail?->business_flow ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="mb-1 text-sm text-zinc-500">
                            Additional Notes
                        </p>

                        <p>
                            {{ $order->detail?->additional_notes ?? '-' }}
                        </p>
                    </div>

                </div>
            </div>

            {{-- Files --}}
            <div class="p-6 bg-white border shadow-sm rounded-2xl dark:bg-zinc-900 dark:border-zinc-800">

                <div class="flex items-center justify-between mb-5">

                    <h2 class="text-lg font-semibold">
                        Uploaded Files
                    </h2>

                    <flux:badge color="zinc">
                        {{ $order->files->count() }} Files
                    </flux:badge>

                </div>

                <div class="space-y-3">

                    @forelse($order->files as $file)

                        <div class="flex items-center justify-between p-4 border rounded-xl dark:border-zinc-800">

                            <div>

                                <p class="font-medium">
                                    {{ $file->file_name }}
                                </p>

                                <p class="text-sm text-zinc-500">
                                    {{ $file->file_type }}
                                </p>

                            </div>

                            <flux:button
                                size="sm"
                                variant="ghost"
                            >
                                Download
                            </flux:button>

                        </div>

                    @empty

                        <p class="text-sm text-zinc-500">
                            No uploaded files
                        </p>

                    @endforelse

                </div>

            </div>

        </div>

        {{-- Right --}}
        <div class="space-y-6">

            {{-- Status History --}}
            <div class="p-6 bg-white border shadow-sm rounded-2xl dark:bg-zinc-900 dark:border-zinc-800">

                <h2 class="mb-5 text-lg font-semibold">
                    Status History
                </h2>

                <div class="space-y-4">

                    @foreach($order->statusHistories as $history)

                        <div class="pb-4 border-b last:border-none dark:border-zinc-800">

                            <div class="flex items-center justify-between mb-1">

                                <p class="font-medium">
                                    {{ str($history->new_status)->replace('_', ' ')->title() }}
                                </p>

                                <p class="text-xs text-zinc-500">
                                    {{ $history->created_at->diffForHumans() }}
                                </p>

                            </div>

                            <p class="text-sm text-zinc-500">
                                By {{ $history->changedBy?->name }}
                            </p>

                            @if($history->note)

                                <p class="mt-2 text-sm">
                                    {{ $history->note }}
                                </p>

                            @endif

                        </div>

                    @endforeach

                </div>

            </div>

            {{-- Payments --}}
            <div class="p-6 bg-white border shadow-sm rounded-2xl dark:bg-zinc-900 dark:border-zinc-800">

                <div class="flex items-center justify-between mb-5">

                    <h2 class="text-lg font-semibold">
                        Payments
                    </h2>

                    <flux:badge color="green">
                        {{ $order->payments->count() }}
                    </flux:badge>

                </div>

                <div class="space-y-3">

                    @forelse($order->payments as $payment)

                        <div class="p-4 border rounded-xl dark:border-zinc-800">

                            <div class="flex items-center justify-between mb-2">

                                <p class="font-semibold">
                                    Rp {{ number_format($payment->amount) }}
                                </p>

                                <flux:badge
                                    :color="$payment->status === 'paid' ? 'green' : 'yellow'"
                                >
                                    {{ ucfirst($payment->status) }}
                                </flux:badge>

                            </div>

                            <p class="text-sm text-zinc-500">
                                {{ $payment->invoice_number }}
                            </p>

                        </div>

                    @empty

                        <p class="text-sm text-zinc-500">
                            No payment data
                        </p>

                    @endforelse

                    @if($order->payments->isEmpty() && $order->estimated_price > 0)
                        <flux:button
                            variant="primary"
                            size="sm"
                            class="w-full mt-4"
                            wire:click="generateInvoice"
                        >
                            Generate Invoice
                        </flux:button>
                    @endif

                </div>

            </div>

        </div>

    </div>

    <flux:modal wire:model="showStatusModal" class="md:w-[500px]">

    <div class="space-y-6">

        <div>

            <flux:heading size="lg">
                Update Order Status
            </flux:heading>

            <flux:text class="mt-2">
                Update status and progress for this order.
            </flux:text>

        </div>

        <flux:select wire:model="status" label="Status">

            <option value="pending">Pending</option>
            <option value="review">Review</option>
            <option value="approved">Approved</option>
            <option value="in_progress">In Progress</option>
            <option value="revision">Revision</option>
            <option value="done">Done</option>
            <option value="rejected">Rejected</option>

        </flux:select>

        <flux:input
            type="number"
            wire:model="estimated_price"
            label="Estimated Price (Rp)"
            placeholder="Enter fixed/estimated price..."
        />

        <flux:input
            type="number"
            wire:model="progress"
            label="Progress (%)"
        />

        <flux:textarea
            wire:model="note"
            label="Note"
            rows="4"
            placeholder="Add update note..."
        />

        <div class="flex justify-end gap-3">

            <flux:button
                variant="ghost"
                wire:click="$set('showStatusModal', false)"
            >
                Cancel
            </flux:button>

            <flux:button
                variant="primary"
                wire:click="updateStatus"
            >
                Save Changes
            </flux:button>

        </div>

    </div>

</flux:modal>

</div>

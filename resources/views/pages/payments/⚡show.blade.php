<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Payment;
use Illuminate\Support\Facades\Validator;
use App\Services\NotificationService;

new class extends Component
{
    public Payment $payment;

    public string $paymentMethod = '';
    public string $notes = '';

    public function mount(Payment $payment): void
    {
        $this->payment = $payment->load(['order.user']);
        $this->paymentMethod = $payment->payment_method ?? '';
        
        // If paid, payment_proof might store the proof path or notes
        if ($payment->status === 'paid' && !str_starts_with($payment->payment_proof ?? '', 'payments/')) {
            $this->notes = $payment->payment_proof ?? '';
        }
    }

    public function confirmPaid(): void
    {
        $validator = Validator::make(
            ['paymentMethod' => $this->paymentMethod],
            ['paymentMethod' => 'required|string|max:100'],
            ['paymentMethod.required' => 'Pilih metode pembayaran.']
        );

        if ($validator->fails()) {
            $this->addError('paymentMethod', $validator->errors()->first('paymentMethod'));
            return;
        }

        $this->payment->update([
            'status'         => 'paid',
            'payment_method' => $this->paymentMethod,
            'payment_proof'  => $this->notes ?: $this->payment->payment_proof,
            'paid_at'        => now(),
        ]);

        // Event handler: saat tagihan di-approve (paid), ubah status order terkait menjadi in_progress secara otomatis
        $order = $this->payment->order;
        if ($order && $order->status !== 'in_progress') {
            $oldStatus = $order->status;
            $order->update([
                'status' => 'in_progress',
                'progress' => max($order->progress, 10), // Set progress to 10% on payment confirmation
                'started_at' => now(),
            ]);

            $order->statusHistories()->create([
                'old_status' => $oldStatus,
                'new_status' => 'in_progress',
                'changed_by' => auth()->id(),
                'note' => 'System: Pembayaran invoice ' . $this->payment->invoice_number . ' telah dikonfirmasi.',
            ]);

            // Notify user about order status transition (Fase 6)
            app(NotificationService::class)->send(
                $order->user,
                'Order Diproses',
                "Pembayaran Anda berhasil diverifikasi. Pesanan '{$order->title}' Anda sekarang sedang dikerjakan!"
            );
        }

        // Send payment confirmation notification (Fase 6)
        app(NotificationService::class)->send(
            $this->payment->order->user,
            'Pembayaran Berhasil',
            "Pembayaran Anda untuk invoice '{$this->payment->invoice_number}' sebesar Rp " . number_format($this->payment->amount) . " telah dikonfirmasi dan diverifikasi oleh Admin."
        );

        $this->payment->refresh();
        $this->dispatch('notify', type: 'success', message: 'Pembayaran dikonfirmasi lunas.');
    }

    public function rejectPayment(): void
    {
        $this->payment->update([
            'status'  => 'failed',
            'paid_at' => null,
        ]);

        // Notify client that payment is rejected (Fase 6)
        app(NotificationService::class)->send(
            $this->payment->order->user,
            'Pembayaran Ditolak',
            "Bukti pembayaran Anda untuk invoice '{$this->payment->invoice_number}' ditolak. Silakan unggah bukti pembayaran yang valid."
        );

        $this->payment->refresh();
        $this->dispatch('notify', type: 'warning', message: 'Pembayaran ditolak.');
    }
};
?>

<div class="space-y-6">
    {{-- Back Link --}}
    <div class="flex items-center gap-2">
        <flux:button
            variant="ghost"
            icon="arrow-left"
            size="sm"
            href="{{ route('payments.index') }}"
            wire:navigate
        >
            Back to Payments
        </flux:button>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_380px]">
        {{-- Left Column: Payment Details --}}
        <div class="space-y-6">
            <div class="rounded-2xl border border-zinc-200 bg-white p-8 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-6 dark:border-zinc-800">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Invoice Number</p>
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $payment->invoice_number }}</h2>
                    </div>
                    <flux:badge size="lg" color="{{ match($payment->status) {
                        'pending' => 'yellow',
                        'paid' => 'green',
                        'failed' => 'red',
                        'refund' => 'zinc',
                        default => 'zinc',
                    } }}">
                        {{ str($payment->status)->title() }}
                    </flux:badge>
                </div>

                <div class="grid gap-8 py-8 sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Client Details</p>
                        <div class="mt-2 space-y-1">
                            <p class="font-semibold text-zinc-900 dark:text-white">{{ $payment->order->user->name }}</p>
                            <p class="text-sm text-zinc-500">{{ $payment->order->user->email }}</p>
                            <p class="text-sm text-zinc-500">{{ $payment->order->user->phone ?? 'No phone number' }}</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Order Details</p>
                        <div class="mt-2 space-y-1">
                            <p class="font-semibold text-zinc-900 dark:text-white">{{ $payment->order->title }}</p>
                            <p class="text-sm text-zinc-500">Order Code: {{ $payment->order->order_code }}</p>
                            <p class="text-sm text-zinc-500">
                                Status:
                                <span class="font-medium text-zinc-700 dark:text-zinc-300">
                                    {{ str($payment->order->status)->replace('_', ' ')->title() }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl bg-zinc-50 p-6 dark:bg-zinc-800/50">
                    <div class="flex items-center justify-between">
                        <span class="text-zinc-500">Total Amount Due</span>
                        <span class="text-3xl font-bold text-zinc-900 dark:text-white">
                            Rp {{ number_format($payment->amount, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                @if($payment->payment_proof)
                    <div class="mt-6 border-t border-zinc-100 pt-6 dark:border-zinc-800">
                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-3">Bukti Pembayaran (Payment Proof)</p>
                        @if(str_starts_with($payment->payment_proof, 'payments/'))
                            <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-800 max-h-96 flex justify-center bg-zinc-100 dark:bg-zinc-850">
                                <img src="{{ Storage::disk('public')->url($payment->payment_proof) }}" alt="Bukti Transfer" class="object-contain max-h-96 w-full">
                            </div>
                        @else
                            <div class="rounded-xl bg-zinc-100 p-4 dark:bg-zinc-800 text-sm text-zinc-700 dark:text-zinc-300">
                                {{ $payment->payment_proof }}
                            </div>
                        @endif
                    </div>
                @endif

                @if($payment->status === 'paid')
                    <div class="mt-8 border-t border-zinc-100 pt-8 dark:border-zinc-800">
                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Payment Information</p>
                        <div class="mt-4 grid gap-6 sm:grid-cols-2">
                            <div>
                                <p class="text-sm text-zinc-500">Method</p>
                                <p class="font-medium text-zinc-900 dark:text-white">{{ $payment->payment_method }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-zinc-500">Paid At</p>
                                <p class="font-medium text-zinc-900 dark:text-white">{{ $payment->paid_at?->format('d M Y H:i') }}</p>
                            </div>
                            @if($payment->payment_proof && !str_starts_with($payment->payment_proof, 'payments/'))
                                <div class="sm:col-span-2">
                                    <p class="text-sm text-zinc-500">Notes/Remarks</p>
                                    <p class="mt-1 text-sm text-zinc-700 dark:text-zinc-300">{{ $payment->payment_proof }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            {{-- Order Progress Section --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-zinc-900 dark:text-white">Order Progress</h3>
                    <flux:button
                        variant="ghost"
                        size="sm"
                        icon="arrow-right"
                        href="{{ route('orders.show', $payment->order) }}"
                        wire:navigate
                    >
                        View Order Detail
                    </flux:button>
                </div>

                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-500">{{ str($payment->order->status)->replace('_', ' ')->title() }}</span>
                        <span class="font-medium">{{ $payment->order->progress }}%</span>
                    </div>
                    <div class="h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                        <div
                            class="h-full bg-blue-500 transition-all duration-500"
                            style="width: {{ $payment->order->progress }}%"
                        ></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Confirmation Panel --}}
        <div class="space-y-6">
            @if($payment->status === 'pending')
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <h3 class="flex items-center gap-2 font-semibold text-zinc-900 dark:text-white mb-6">
                        <flux:icon name="shield-check" class="size-5 text-zinc-400" />
                        Verification
                    </h3>

                    <div class="space-y-4">
                        <flux:select wire:model="paymentMethod" label="Payment Method" placeholder="Select method...">
                            <flux:select.option value="Cash">Cash</flux:select.option>
                            <flux:select.option value="Transfer Bank BCA">Transfer Bank BCA</flux:select.option>
                            <flux:select.option value="Transfer Bank Mandiri">Transfer Bank Mandiri</flux:select.option>
                            <flux:select.option value="Transfer Bank BRI">Transfer Bank BRI</flux:select.option>
                            <flux:select.option value="Transfer Bank BNI">Transfer Bank BNI</flux:select.option>
                            <flux:select.option value="QRIS">QRIS</flux:select.option>
                            <flux:select.option value="Lainnya">Lainnya</flux:select.option>
                        </flux:select>

                        <flux:textarea
                            wire:model="notes"
                            label="Notes (Optional)"
                            placeholder="Add payment notes or remarks..."
                            rows="3"
                        />

                        <div class="space-y-2 pt-4">
                            <flux:button
                                wire:click="confirmPaid"
                                variant="primary"
                                class="w-full"
                                icon="check-circle"
                                wire:loading.attr="disabled"
                            >
                                Confirm Paid
                            </flux:button>
                            <flux:button
                                wire:click="rejectPayment"
                                variant="ghost"
                                class="w-full text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-500/10"
                                icon="x-circle"
                                wire:loading.attr="disabled"
                            >
                                Reject Payment
                            </flux:button>
                        </div>
                    </div>
                </div>
            @else
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex flex-col items-center justify-center py-6 text-center">
                        @if($payment->status === 'paid')
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400 mb-4">
                                <flux:icon name="check-badge" class="size-10" />
                            </div>
                            <h3 class="text-lg font-bold text-zinc-900 dark:text-white">Payment Verified</h3>
                            <p class="mt-1 text-sm text-zinc-500">This invoice has been marked as paid.</p>
                        @elseif($payment->status === 'failed')
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-500/10 dark:text-red-400 mb-4">
                                <flux:icon name="x-circle" class="size-10" />
                            </div>
                            <h3 class="text-lg font-bold text-zinc-900 dark:text-white">Payment Rejected</h3>
                            <p class="mt-1 text-sm text-zinc-500">This payment attempt was rejected.</p>
                        @else
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 text-zinc-600 dark:bg-zinc-500/10 dark:text-zinc-400 mb-4">
                                <flux:icon name="minus-circle" class="size-10" />
                            </div>
                            <h3 class="text-lg font-bold text-zinc-900 dark:text-white">Payment Status</h3>
                            <p class="mt-1 text-sm text-zinc-500">Status: {{ str($payment->status)->title() }}</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

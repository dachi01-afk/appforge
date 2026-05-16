<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedStatusFilter(): void { $this->resetPage(); }

    #[Computed]
    public function payments()
    {
        return Payment::query()
            ->with(['order.user'])
            ->when($this->search, fn($q) => $q
                ->where('invoice_number', 'like', '%' . $this->search . '%')
                ->orWhereHas('order.user', fn($q2) => $q2->where('name', 'like', '%' . $this->search . '%'))
                ->orWhereHas('order', fn($q2) => $q2->where('title', 'like', '%' . $this->search . '%'))
            )
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'total_revenue'  => Payment::where('status', 'paid')->sum('amount'),
            'total_pending'  => Payment::where('status', 'pending')->count(),
            'total_paid'     => Payment::where('status', 'paid')->count(),
            'total_failed'   => Payment::where('status', 'failed')->count(),
        ];
    }
};
?>

<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Payments</h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Manage invoices and verify client payments.
            </p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                    <flux:icon name="banknotes" class="size-6" />
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Total Revenue</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">Rp {{ number_format($this->stats['total_revenue'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-yellow-50 text-yellow-600 dark:bg-yellow-500/10 dark:text-yellow-400">
                    <flux:icon name="clock" class="size-6" />
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Pending</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $this->stats['total_pending'] }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                    <flux:icon name="check-circle" class="size-6" />
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Paid</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $this->stats['total_paid'] }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                    <flux:icon name="x-circle" class="size-6" />
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Failed</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $this->stats['total_failed'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters & Table --}}
    <div class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900 overflow-hidden">
        {{-- Table Filters --}}
        <div class="flex flex-col gap-4 border-b border-zinc-200 p-4 dark:border-zinc-800 sm:flex-row sm:items-center sm:justify-between">
            <div class="w-full sm:max-w-xs">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    icon="magnifying-glass"
                    placeholder="Search invoice or client..."
                    size="sm"
                />
            </div>
            <div class="flex items-center gap-2">
                <flux:select wire:model.live="statusFilter" size="sm" class="w-40">
                    <flux:select.option value="">All Statuses</flux:select.option>
                    <flux:select.option value="pending">Pending</flux:select.option>
                    <flux:select.option value="paid">Paid</flux:select.option>
                    <flux:select.option value="failed">Failed</flux:select.option>
                    <flux:select.option value="refund">Refund</flux:select.option>
                </flux:select>
            </div>
        </div>

        {{-- Payments Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 text-xs font-semibold text-zinc-500 uppercase tracking-wider dark:bg-zinc-800/50">
                    <tr>
                        <th class="px-6 py-4">Invoice</th>
                        <th class="px-6 py-4">Client</th>
                        <th class="px-6 py-4">Method</th>
                        <th class="px-6 py-4 text-right">Amount</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @forelse($this->payments as $payment)
                        <tr class="group hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-zinc-900 dark:text-white">{{ $payment->invoice_number }}</div>
                                <div class="text-xs text-zinc-500">{{ $payment->order->title }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-zinc-900 dark:text-white">{{ $payment->order->user->name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($payment->payment_method)
                                    <flux:badge size="sm" variant="outline">{{ $payment->payment_method }}</flux:badge>
                                @else
                                    <span class="text-zinc-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-medium text-zinc-900 dark:text-white">
                                Rp {{ number_format($payment->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4">
                                <flux:badge size="sm" color="{{ match($payment->status) {
                                    'pending' => 'yellow',
                                    'paid' => 'green',
                                    'failed' => 'red',
                                    'refund' => 'zinc',
                                    default => 'zinc',
                                } }}">
                                    {{ str($payment->status)->title() }}
                                </flux:badge>
                            </td>
                            <td class="px-6 py-4 text-zinc-500">
                                {{ $payment->created_at->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="eye"
                                    href="{{ route('payments.show', $payment) }}"
                                    wire:navigate
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-zinc-500">
                                No payments found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($this->payments->hasPages())
            <div class="border-t border-zinc-200 p-4 dark:border-zinc-800">
                {{ $this->payments->links() }}
            </div>
        @endif
    </div>
</div>

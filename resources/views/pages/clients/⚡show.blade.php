<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\User;
use App\Models\Payment;

new class extends Component
{
    public User $client;

    public function mount(User $user): void
    {
        // Guard: pastikan yang dibuka adalah client, bukan admin
        if ($user->role !== 'client') {
            abort(404);
        }
        $this->client = $user->load(['orders.payments']);
    }

    #[Computed]
    public function orders()
    {
        return $this->client->orders()
            ->with(['payments'])
            ->latest()
            ->get();
    }

    #[Computed]
    public function stats(): array
    {
        $orders = $this->client->orders;
        return [
            'total_orders'   => $orders->count(),
            'active_orders'  => $orders->whereNotIn('status', ['done', 'rejected'])->count(),
            'total_paid'     => $orders->flatMap->payments->where('status', 'paid')->sum('amount'),
            'total_revenue'  => $orders->flatMap->payments->sum('amount'),
        ];
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
            href="{{ route('clients.index') }}"
            wire:navigate
        >
            Back to Clients
        </flux:button>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_340px]">
        {{-- Left Column: Order History --}}
        <div class="space-y-6">
            <div class="rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900 overflow-hidden">
                <div class="border-b border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-800 dark:bg-zinc-800/50">
                    <h3 class="font-semibold text-zinc-900 dark:text-white">Order History</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-zinc-50 text-xs font-semibold text-zinc-500 uppercase tracking-wider dark:bg-zinc-800/50">
                            <tr>
                                <th class="px-4 py-3">Code</th>
                                <th class="px-4 py-3">Project Title</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-right">Budget</th>
                                <th class="px-4 py-3">Progress</th>
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @forelse($this->orders as $order)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-4 py-4 font-mono text-xs font-semibold text-zinc-600 dark:text-zinc-400">
                                        {{ $order->order_code }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-zinc-900 dark:text-white truncate max-w-[200px]">
                                            {{ $order->title }}
                                        </div>
                                        <div class="text-xs text-zinc-500">
                                            {{ $order->created_at->format('d M Y') }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <flux:badge size="sm" color="{{ match($order->status) {
                                            'pending'     => 'yellow',
                                            'review'      => 'zinc',
                                            'approved'    => 'green',
                                            'in_progress' => 'blue',
                                            'revision'    => 'orange',
                                            'done'        => 'emerald',
                                            'rejected'    => 'red',
                                            default       => 'zinc',
                                        } }}">
                                            {{ str($order->status)->replace('_', ' ')->title() }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-4 py-4 text-right font-medium text-zinc-900 dark:text-white whitespace-nowrap text-xs">
                                        Rp {{ number_format($order->budget, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="w-24 space-y-1.5">
                                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                                <div
                                                    class="h-full bg-blue-500 transition-all duration-500"
                                                    style="width: {{ $order->progress }}%"
                                                ></div>
                                            </div>
                                            <div class="text-[10px] text-zinc-400 text-right">{{ $order->progress }}%</div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        <flux:button
                                            size="xs"
                                            variant="ghost"
                                            icon="eye"
                                            href="{{ route('orders.show', $order) }}"
                                            wire:navigate
                                        />
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-center text-zinc-500">
                                        No orders found for this client.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right Column: Profile & Summary --}}
        <div class="space-y-6">
            {{-- Profile Card --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex flex-col items-center text-center">
                    <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-zinc-100 text-2xl font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 mb-4">
                        {{ $client->initials() }}
                    </div>
                    <h3 class="text-xl font-bold text-zinc-900 dark:text-white">{{ $client->name }}</h3>
                    <p class="text-sm text-zinc-500">{{ $client->email }}</p>

                    <div class="mt-6 w-full space-y-3 pt-6 border-t border-zinc-100 dark:border-zinc-800">
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-500">Phone</span>
                            <span class="font-medium text-zinc-900 dark:text-white">{{ $client->phone ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-500">Joined</span>
                            <span class="font-medium text-zinc-900 dark:text-white">{{ $client->created_at->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Summary Card --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <h4 class="font-semibold text-zinc-900 dark:text-white mb-4 flex items-center gap-2">
                    <flux:icon name="chart-bar" class="size-4 text-zinc-400" />
                    Client Summary
                </h4>

                <div class="space-y-4">
                    <div class="rounded-xl bg-zinc-50 p-4 dark:bg-zinc-800/50">
                        <p class="text-xs text-zinc-500 uppercase tracking-wider mb-1">Total Paid</p>
                        <p class="text-xl font-bold text-zinc-900 dark:text-white">
                            Rp {{ number_format($this->stats['total_paid'], 0, ',', '.') }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-xl border border-zinc-100 p-4 dark:border-zinc-800">
                            <p class="text-xs text-zinc-500 mb-1">Total Orders</p>
                            <p class="text-lg font-bold">{{ $this->stats['total_orders'] }}</p>
                        </div>
                        <div class="rounded-xl border border-zinc-100 p-4 dark:border-zinc-800">
                            <p class="text-xs text-zinc-500 mb-1">Active</p>
                            <p class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ $this->stats['active_orders'] }}</p>
                        </div>
                    </div>

                    <div class="pt-2">
                        <div class="flex justify-between text-xs text-zinc-500 mb-2">
                            <span>Total Billing</span>
                            <span>Rp {{ number_format($this->stats['total_revenue'], 0, ',', '.') }}</span>
                        </div>
                        @php
                            $percentage = $this->stats['total_revenue'] > 0
                                ? ($this->stats['total_paid'] / $this->stats['total_revenue']) * 100
                                : 0;
                        @endphp
                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                            <div
                                class="h-full bg-emerald-500 transition-all duration-500"
                                style="width: {{ $percentage }}%"
                            ></div>
                        </div>
                        <p class="mt-2 text-[10px] text-zinc-400 text-center">
                            {{ number_format($percentage, 1) }}% of total billing collected
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

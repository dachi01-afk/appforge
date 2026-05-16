<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Order;
use App\Models\Payment;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $orderFilter = ''; // '' | 'has_orders' | 'no_orders'

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedOrderFilter(): void { $this->resetPage(); }

    #[Computed]
    public function clients()
    {
        return User::query()
            ->where('role', 'client')
            ->withCount('orders')
            ->withSum(['payments as total_paid' => fn($q) => $q->where('payments.status', 'paid')], 'amount')
            ->when($this->search, fn($q) => $q
                ->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%')
            )
            ->when($this->orderFilter === 'has_orders', fn($q) => $q->has('orders'))
            ->when($this->orderFilter === 'no_orders', fn($q) => $q->doesntHave('orders'))
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'total_clients'  => User::where('role', 'client')->count(),
            'active_orders'  => Order::whereNotIn('status', ['done', 'rejected'])->count(),
            'total_revenue'  => Payment::where('status', 'paid')->sum('amount'),
        ];
    }
};
?>

<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Clients</h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Manage registered clients and their project history.
            </p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                    <flux:icon name="users" class="size-6" />
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Total Clients</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $this->stats['total_clients'] }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400">
                    <flux:icon name="shopping-bag" class="size-6" />
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Active Orders</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $this->stats['active_orders'] }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                    <flux:icon name="currency-dollar" class="size-6" />
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Total Revenue</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">Rp {{ number_format($this->stats['total_revenue'], 0, ',', '.') }}</p>
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
                    placeholder="Search name or email..."
                    size="sm"
                />
            </div>
            <div class="flex items-center gap-2">
                <flux:select wire:model.live="orderFilter" size="sm" class="w-48">
                    <flux:select.option value="">All Clients</flux:select.option>
                    <flux:select.option value="has_orders">Has Orders</flux:select.option>
                    <flux:select.option value="no_orders">No Orders</flux:select.option>
                </flux:select>
            </div>
        </div>

        {{-- Clients Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 text-xs font-semibold text-zinc-500 uppercase tracking-wider dark:bg-zinc-800/50">
                    <tr>
                        <th class="px-6 py-4">Client</th>
                        <th class="px-6 py-4">Phone</th>
                        <th class="px-6 py-4 text-center">Orders</th>
                        <th class="px-6 py-4 text-right">Total Paid</th>
                        <th class="px-6 py-4">Joined At</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @forelse($this->clients as $client)
                        <tr class="group hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-xs font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                        {{ $client->initials() }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-zinc-900 dark:text-white truncate">{{ $client->name }}</div>
                                        <div class="text-xs text-zinc-500 truncate">{{ $client->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-zinc-500">
                                {{ $client->phone ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <flux:badge size="sm" variant="outline">{{ $client->orders_count }}</flux:badge>
                            </td>
                            <td class="px-6 py-4 text-right font-medium text-zinc-900 dark:text-white">
                                Rp {{ number_format($client->total_paid ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-zinc-500 text-xs">
                                {{ $client->created_at->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="eye"
                                    href="{{ route('clients.show', $client) }}"
                                    wire:navigate
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-zinc-500">
                                No clients found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($this->clients->hasPages())
            <div class="border-t border-zinc-200 p-4 dark:border-zinc-800">
                {{ $this->clients->links() }}
            </div>
        @endif
    </div>
</div>

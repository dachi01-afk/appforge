<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Message;
use App\Models\User;
use App\Models\OrderStatusHistory;

new class extends Component
{
    #[Computed]
    public function stats(): array
    {
        return [
            'total_orders'      => Order::count(),
            'active_orders'     => Order::whereIn('status', ['approved', 'in_progress', 'revision'])->count(),
            'pending_review'    => Order::whereIn('status', ['pending', 'review'])->count(),
            'total_revenue'     => Payment::where('status', 'paid')->sum('amount'),
            'pending_payments'  => Payment::where('status', 'pending')->count(),
            'total_clients'     => User::where('role', 'client')->count(),
        ];
    }

    #[Computed]
    public function recentOrders()
    {
        return Order::with(['user'])
            ->latest()
            ->take(5)
            ->get();
    }

    #[Computed]
    public function recentPayments()
    {
        return Payment::with(['order.user'])
            ->latest()
            ->take(5)
            ->get();
    }

    #[Computed]
    public function unreadMessages()
    {
        return Message::with(['sender', 'order'])
            ->where('is_read', false)
            ->latest()
            ->take(5)
            ->get();
    }

    #[Computed]
    public function unreadCount(): int
    {
        return Message::where('is_read', false)->count();
    }

    #[Computed]
    public function recentActivities()
    {
        return OrderStatusHistory::with(['order', 'changer'])
            ->latest()
            ->take(5)
            ->get();
    }

    public function getGreeting(): string
    {
        $hour = now()->hour;
        if ($hour < 12) return 'Good morning';
        if ($hour < 17) return 'Good afternoon';
        return 'Good evening';
    }
};
?>

<div class="space-y-8 pb-12">
    {{-- Header --}}
    <div>
        <h1 class="text-3xl font-bold tracking-tight">{{ $this->getGreeting() }}, {{ auth()->user()->name }}! 👋</h1>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
            {{ now()->format('l, d F Y') }} • Here's what's happening with AppForge today.
        </p>
    </div>

    {{-- KPI Stats --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        {{-- Total Orders --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                    <flux:icon name="shopping-bag" class="size-5" />
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Total Orders</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $this->stats['total_orders'] }}</p>
                </div>
            </div>
        </div>

        {{-- Active Orders --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                    <flux:icon name="play-circle" class="size-5" />
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Active</p>
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $this->stats['active_orders'] }}</p>
                </div>
            </div>
        </div>

        {{-- Pending Review --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-yellow-50 text-yellow-600 dark:bg-yellow-500/10 dark:text-yellow-400">
                    <flux:icon name="clock" class="size-5" />
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Review</p>
                    <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-500">{{ $this->stats['pending_review'] }}</p>
                </div>
            </div>
        </div>

        {{-- Total Revenue --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                    <flux:icon name="currency-dollar" class="size-5" />
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Revenue</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">Rp {{ number_format($this->stats['total_revenue'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        {{-- Pending Payments --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400">
                    <flux:icon name="credit-card" class="size-5" />
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Pend. Pay</p>
                    <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $this->stats['pending_payments'] }}</p>
                </div>
            </div>
        </div>

        {{-- Total Clients --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400">
                    <flux:icon name="users" class="size-5" />
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Clients</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $this->stats['total_clients'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Recent Orders --}}
        <div class="rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900 overflow-hidden">
            <div class="flex items-center justify-between border-b border-zinc-100 p-5 dark:border-zinc-800">
                <h3 class="font-semibold text-zinc-900 dark:text-white">Recent Orders</h3>
                <flux:button variant="ghost" size="sm" href="{{ route('orders.index') }}" wire:navigate>View All</flux:button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach($this->recentOrders as $order)
                            <tr class="group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                                <td class="px-5 py-4">
                                    <div class="font-medium text-zinc-900 dark:text-white truncate max-w-[180px]">{{ $order->title }}</div>
                                    <div class="text-[10px] font-mono text-zinc-400">{{ $order->order_code }}</div>
                                </td>
                                <td class="px-5 py-4">
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
                                <td class="px-5 py-4">
                                    <div class="w-20">
                                        <div class="h-1 w-full rounded-full bg-zinc-100 dark:bg-zinc-800">
                                            <div class="h-full bg-blue-500" style="width: {{ $order->progress }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <flux:button size="xs" variant="ghost" icon="eye" href="{{ route('orders.show', $order) }}" wire:navigate />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Payments --}}
        <div class="rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900 overflow-hidden">
            <div class="flex items-center justify-between border-b border-zinc-100 p-5 dark:border-zinc-800">
                <h3 class="font-semibold text-zinc-900 dark:text-white">Recent Payments</h3>
                <flux:button variant="ghost" size="sm" href="{{ route('payments.index') }}" wire:navigate>View All</flux:button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach($this->recentPayments as $payment)
                            <tr class="group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                                <td class="px-5 py-4">
                                    <div class="font-medium text-zinc-900 dark:text-white">{{ $payment->invoice_number }}</div>
                                    <div class="text-[10px] text-zinc-400">{{ $payment->order->user->name }}</div>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="font-semibold text-zinc-900 dark:text-white">Rp {{ number_format($payment->amount, 0, ',', '.') }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <flux:badge size="sm" color="{{ match($payment->status) {
                                        'paid' => 'green',
                                        'pending' => 'yellow',
                                        'failed' => 'red',
                                        default => 'zinc',
                                    } }}">
                                        {{ str($payment->status)->title() }}
                                    </flux:badge>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <flux:button size="xs" variant="ghost" icon="eye" href="{{ route('payments.show', $payment) }}" wire:navigate />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Recent Activity --}}
        <div class="lg:col-span-2 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <h3 class="font-semibold text-zinc-900 dark:text-white mb-6">Recent Activity</h3>
            <div class="space-y-6">
                @forelse($this->recentActivities as $activity)
                    <div class="relative flex gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-zinc-50 text-zinc-400 dark:bg-zinc-800/50">
                            <flux:icon name="arrow-path" class="size-5" />
                        </div>
                        <div class="min-w-0 flex-1 pt-1.5">
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                <span class="font-semibold text-zinc-900 dark:text-white">{{ $activity->changer->name }}</span>
                                updated status of
                                <span class="font-medium text-zinc-900 dark:text-white">{{ $activity->order->title }}</span>
                            </p>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="text-[10px] font-medium text-zinc-400 uppercase tracking-wider">{{ $activity->old_status }}</span>
                                <flux:icon name="arrow-right" class="size-3 text-zinc-300" />
                                <span class="text-[10px] font-bold text-blue-500 uppercase tracking-wider">{{ $activity->new_status }}</span>
                                <span class="text-zinc-300">•</span>
                                <span class="text-[10px] text-zinc-400">{{ $activity->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-zinc-500 py-4 text-center">No recent activities found.</p>
                @endforelse
            </div>
        </div>

        {{-- Unread Messages --}}
        <div class="rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-100 p-5 dark:border-zinc-800">
                <div class="flex items-center gap-2">
                    <h3 class="font-semibold text-zinc-900 dark:text-white">Unread Messages</h3>
                    @if($this->unreadCount > 0)
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
                            {{ $this->unreadCount }}
                        </span>
                    @endif
                </div>
                <flux:button variant="ghost" size="sm" href="{{ route('inbox.index') }}" wire:navigate>Go to Inbox</flux:button>
            </div>
            <div class="p-2 space-y-1">
                @forelse($this->unreadMessages as $message)
                    <a href="{{ route('inbox.index') }}" wire:navigate class="block rounded-xl p-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                        <div class="flex justify-between items-start mb-1">
                            <span class="text-xs font-bold text-zinc-900 dark:text-white">{{ $message->sender->name }}</span>
                            <span class="text-[10px] text-zinc-400">{{ $message->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-xs text-zinc-600 dark:text-zinc-400 line-clamp-2 leading-relaxed">
                            {{ $message->message }}
                        </p>
                        <div class="mt-2 text-[9px] font-medium text-zinc-400 uppercase tracking-tighter">
                            Ref: {{ $message->order->order_code }}
                        </div>
                    </a>
                @empty
                    <div class="flex flex-col items-center justify-center py-10 text-center">
                        <flux:icon name="chat-bubble-left-right" class="size-10 text-zinc-200 dark:text-zinc-800 mb-3" />
                        <p class="text-xs text-zinc-400">All caught up!</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

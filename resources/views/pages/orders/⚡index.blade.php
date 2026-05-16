<?php
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Models\Order;

new class extends Component
{
     use WithPagination;

    public string $search = '';

    public string $status = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function orders()
    {
      return Order::query()
            ->with('user')
            ->when($this->search, function ($query) {

                $query->where(function ($query) {

                    $query->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('order_code', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status, function ($query) {

                $query->where('status', $this->status);
            })
            ->latest()
            ->paginate(10);

    }
};
?>

<div>
    <div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

        <div>

            <h1 class="text-3xl font-bold tracking-tight">
                Orders
            </h1>

            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                Manage all client orders and monitor project progress.
            </p>

        </div>

    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-2xl border bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

            <p class="text-sm text-zinc-500">
                Total Orders
            </p>

            <h2 class="mt-2 text-3xl font-bold">
                {{ $this->orders->total() }}
            </h2>

        </div>

        <div class="rounded-2xl border bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

            <p class="text-sm text-zinc-500">
                Pending
            </p>

            <h2 class="mt-2 text-3xl font-bold text-yellow-500">
                12
            </h2>

        </div>

        <div class="rounded-2xl border bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

            <p class="text-sm text-zinc-500">
                In Progress
            </p>

            <h2 class="mt-2 text-3xl font-bold text-blue-500">
                8
            </h2>

        </div>

        <div class="rounded-2xl border bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

            <p class="text-sm text-zinc-500">
                Completed
            </p>

            <h2 class="mt-2 text-3xl font-bold text-green-500">
                25
            </h2>

        </div>

    </div>

    {{-- Filters --}}
    <div class="flex flex-col gap-4 md:flex-row">

        <div class="flex-1">

            <flux:input
                wire:model.live.debounce.500ms="search"
                icon="magnifying-glass"
                placeholder="Search order..."
            />

        </div>

        <div class="w-full md:w-60">

            <flux:select wire:model.live="status">

                <option value="">
                    All Status
                </option>

                <option value="pending">
                    Pending
                </option>

                <option value="review">
                    Review
                </option>

                <option value="approved">
                    Approved
                </option>

                <option value="in_progress">
                    In Progress
                </option>

                <option value="done">
                    Done
                </option>

            </flux:select>

        </div>

    </div>

    {{-- Table Card --}}
    <div class="overflow-hidden rounded-2xl border bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

        <div class="overflow-x-auto">

            <table class="min-w-full">

                <thead class="border-b bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">

                    <tr>

                        <th class="px-6 py-4 text-left text-sm font-semibold">
                            Order
                        </th>

                        <th class="px-6 py-4 text-left text-sm font-semibold">
                            Client
                        </th>

                        <th class="px-6 py-4 text-left text-sm font-semibold">
                            Budget
                        </th>

                        <th class="px-6 py-4 text-left text-sm font-semibold">
                            Status
                        </th>

                        <th class="px-6 py-4 text-left text-sm font-semibold">
                            Progress
                        </th>

                        <th class="px-6 py-4 text-right text-sm font-semibold">
                            Action
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($this->orders as $order)

                        <tr
                            wire:key="{{ $order->id }}"
                            class="border-b transition hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800/50"
                        >

                            {{-- Order --}}
                            <td class="px-6 py-4">

                                <div>

                                    <p class="font-medium">
                                        {{ $order->title }}
                                    </p>

                                    <p class="text-sm text-zinc-500">
                                        {{ $order->order_code }}
                                    </p>

                                </div>

                            </td>

                            {{-- Client --}}
                            <td class="px-6 py-4">

                                <div class="flex items-center gap-3">

                                    <flux:avatar
                                        size="sm"
                                        :name="$order->user->name"
                                    />

                                    <div>

                                        <p class="font-medium">
                                            {{ $order->user->name }}
                                        </p>

                                        <p class="text-sm text-zinc-500">
                                            Client
                                        </p>

                                    </div>

                                </div>

                            </td>

                            {{-- Budget --}}
                            <td class="px-6 py-4">

                                <p class="font-medium">
                                    Rp {{ number_format($order->budget) }}
                                </p>

                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4">

                                <flux:badge
                                    size="sm"
                                    color="
                                        {{ match($order->status) {
                                            'pending' => 'yellow',
                                            'review' => 'zinc',
                                            'approved' => 'green',
                                            'in_progress' => 'blue',
                                            'done' => 'emerald',
                                            default => 'zinc',
                                        } }}
                                    "
                                >

                                    {{ str($order->status)->replace('_', ' ')->title() }}

                                </flux:badge>

                            </td>

                            {{-- Progress --}}
                            <td class="px-6 py-4">

                                <div class="space-y-2">

                                    <div class="flex items-center justify-between text-sm">

                                        <span>
                                            {{ $order->progress }}%
                                        </span>

                                    </div>

                                    <div class="h-2 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">

                                        <div
                                            class="h-full rounded-full bg-black dark:bg-white"
                                            style="width: {{ $order->progress }}%"
                                        ></div>

                                    </div>

                                </div>

                            </td>

                            {{-- Action --}}
                            <td class="px-6 py-4 text-right">

                                <flux:button
                                    size="sm"
                                    variant="primary"
                                    :href="route('orders.show', $order)"
                                    wire:navigate
                                >

                                    Detail

                                </flux:button>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6" class="px-6 py-16 text-center">

                                <div class="flex flex-col items-center gap-3">

                                    <flux:icon
                                        name="inbox"
                                        class="size-12 text-zinc-400"
                                    />

                                    <div>

                                        <p class="font-medium">
                                            No orders found
                                        </p>

                                        <p class="text-sm text-zinc-500">
                                            Try changing your filters
                                        </p>

                                    </div>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

    {{-- Pagination --}}
    <div>

        {{ $this->orders->links() }}

    </div>

</div>

</div>

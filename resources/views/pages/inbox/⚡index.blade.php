<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Order;
use App\Models\Message;

new class extends Component
{
    public ?Order $order = null;

    public string $newMessage = '';

     public function mount(): void
    {
        $this->order = Order::with('user')->latest()->first();
    }

    public function sendMessage(): void
    {
        $this->validate([
            'newMessage' => 'required|string|max:5000',
        ]);

        Message::create([
            'order_id' => $this->order->id,
            'sender_id' => auth()->id(),
            'message' => $this->newMessage,
        ]);

        $this->reset('newMessage');

        // $this->order->refresh();
    }

   public function getMessagesProperty()
    {
        if (!$this->order) {
            return collect();
        }

        return Message::query()
            ->with('sender')
            ->where('order_id', $this->order->id)
            ->latest()
            ->get()
            ->reverse();
    }
};
?>

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        <div>

            <h1 class="text-3xl font-bold tracking-tight">
                Inbox
            </h1>

            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Manage client conversations and project discussions.
            </p>

        </div>

    </div>

    {{-- Main Inbox Layout --}}

        <div class="grid h-[90vh] gap-6 lg:grid-cols-[360px_1fr]">

    {{-- SIDEBAR --}}
    <div
        class="
            flex
            flex-col
            overflow-hidden
            rounded-[28px]
            border
            border-zinc-200
            bg-white
            shadow-sm
            dark:border-zinc-800
            dark:bg-zinc-900
        "
    >

        {{-- Sidebar Header --}}
        <div class="border-b border-zinc-200 p-5 dark:border-zinc-800">

            <div class="space-y-4">

                <div>
                    <p class="mt-1 text-sm text-zinc-500">
                        Active client chats
                    </p>

                </div>

                <div class="flex items-center gap-3">

                    <div class="flex-1">

                        <flux:input
                            icon="magnifying-glass"
                            placeholder="Search message..."
                        />

                    </div>

                    <flux:button
                        variant="primary"
                        icon="plus"
                    />

                </div>

            </div>

        </div>

        {{-- Conversation List --}}
        <div class="flex-1 overflow-y-auto">

            <button
                class="
                    flex
                    w-full
                    items-start
                    gap-4
                    border-b
                    border-zinc-200
                    p-5
                    text-left
                    transition
                    hover:bg-zinc-50
                    dark:border-zinc-800
                    dark:hover:bg-zinc-800/50
                "
            >

                {{-- Avatar --}}
                <div class="relative shrink-0">

                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-zinc-200 font-semibold dark:bg-zinc-700">

                        {{ str($order->user->name)->substr(0, 2) }}

                    </div>

                    <div class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white bg-emerald-500 dark:border-zinc-900"></div>

                </div>

                {{-- Content --}}
                <div class="min-w-0 flex-1">

                    <div class="flex items-center justify-between gap-3">

                        <h3 class="truncate font-semibold">
                            {{ $order->user->name }}
                        </h3>

                        <span class="text-xs text-zinc-500">
                            {{ $order->updated_at->diffForHumans() }}
                        </span>

                    </div>

                    <p class="mt-1 truncate text-sm text-zinc-500">
                        {{ $this->messages->last()?->message ?? 'No message yet' }}
                    </p>

                    <div class="mt-3 flex items-center justify-between">

                        <flux:badge
                            size="sm"
                            color="zinc"
                        >
                            {{ str($order->status)->replace('_', ' ')->title() }}
                        </flux:badge>

                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-black text-xs text-white dark:bg-white dark:text-black">
                            1
                        </div>

                    </div>

                </div>

            </button>

        </div>

    </div>

    {{-- CHAT AREA --}}
    <div
        class="
            flex
            h-full
            flex-col
            overflow-hidden
            rounded-[28px]
            border
            border-zinc-200
            bg-white
            shadow-sm
            dark:border-zinc-800
            dark:bg-zinc-900
        "
    >

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-zinc-200 px-7 py-5 dark:border-zinc-800">

            <div class="flex items-center gap-4">

                <div class="relative">

                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-zinc-200 font-semibold dark:bg-zinc-700">

                        {{ str($order->user->name)->substr(0, 2) }}

                    </div>

                    <div class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white bg-emerald-500 dark:border-zinc-900"></div>

                </div>

                <div>

                    <h2 class="font-semibold">
                        {{ $order->user->name }}
                    </h2>

                    <p class="mt-1 text-sm text-emerald-500">
                        Online now
                    </p>

                </div>

            </div>

            <div class="flex items-center gap-2">

                <flux:button
                    variant="ghost"
                    icon="phone"
                />

                <flux:button
                    variant="ghost"
                    icon="video-camera"
                />

                <flux:button
                    variant="ghost"
                    icon="ellipsis-horizontal"
                />

            </div>

        </div>

        {{-- SCROLLABLE MESSAGES --}}
        <div
            class="
                flex-1
                overflow-y-auto
                bg-zinc-50/50
                px-6
                py-6
                dark:bg-zinc-950/20
                scrollbar-hide
            "
        >

            <div class="mx-auto max-w-4xl space-y-6">

                @foreach($this->messages as $message)

                    @php
                        $isMine = $message->sender_id === auth()->id();
                    @endphp

                    <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">

                        <div class="flex max-w-[62%] items-end gap-2">

                            @unless($isMine)

                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-zinc-300 text-sm font-semibold dark:bg-zinc-700">

                                    {{ str($message->sender->name)->substr(0, 2) }}

                                </div>

                            @endunless

                            <div
                                class="
                                    rounded-2xl
                                    px-4
                                    py-3
                                    shadow-sm
                                    text-[15px]
                                    leading-relaxed
                                    {{ $isMine
                                        ? 'rounded-br-md bg-black text-white dark:bg-white dark:text-black'
                                        : 'rounded-bl-md border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900'
                                    }}
                                "
                            >

                                <p class="leading-relaxed break-words">
                                    {{ $message->message }}
                                </p>

                                <div class="mt-2 flex justify-end text-[11px] text-zinc-400">

                                    {{ $message->created_at->format('H:i') }}

                                </div>

                            </div>

                        </div>

                    </div>

                @endforeach

            </div>

        </div>

        {{-- INPUT --}}
        <div class="border-t border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">

            <div class="flex items-center gap-3">

                {{-- Attachment --}}
                <flux:button
                    variant="ghost"
                    icon="paper-clip"
                    class="shrink-0"
                />

                {{-- Input --}}
                <div class="flex-1">

                    <flux:input
                        wire:model="newMessage"
                        wire:keydown.enter="sendMessage"
                        placeholder="Type your message..."
                        class="rounded-full border-0 bg-zinc-100 dark:bg-zinc-800"
                    />

                </div>

                {{-- Send --}}
                <flux:button
                    variant="primary"
                    icon="paper-airplane"
                    wire:click="sendMessage"
                    class="shrink-0 rounded-full"
                />

            </div>

        </div>

    </div>

</div>

    </div>

</div>

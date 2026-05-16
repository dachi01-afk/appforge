<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Poll;
use App\Models\Order;
use App\Models\Message;
use Illuminate\Support\Facades\Validator;

new class extends Component
{
    // ID order yang sedang dipilih
    public ?int $selectedOrderId = null;

    // Teks pesan yang sedang diketik
    public string $newMessage = '';

    // Input search sidebar
    public string $search = '';

    /**
     * Mount: otomatis pilih order pertama yang ada pesannya (jika ada)
     */
    public function mount(): void
    {
        $first = $this->conversationList->first();
        if ($first) {
            $this->selectedOrderId = $first->id;
            $this->markAsRead();
        }
    }

    /**
     * Computed: daftar order untuk ditampilkan di sidebar
     * Hanya order yang memiliki minimal 1 pesan OR semua order (tergantung kebutuhan)
     * Di sini tampilkan semua order, dengan eager load user dan messages
     */
    #[Computed]
    public function conversationList()
    {
        return Order::query()
            ->with(['user', 'messages' => fn($q) => $q->latest()->limit(1)])
            ->withCount(['messages as unread_count' => fn($q) => $q->where('is_read', false)->where('sender_id', '!=', auth()->id())])
            ->when($this->search, fn($q) => $q->whereHas('user', fn($q2) => $q2->where('name', 'like', '%' . $this->search . '%'))
                ->orWhere('title', 'like', '%' . $this->search . '%'))
            ->latest('updated_at')
            ->get();
    }

    /**
     * Computed: order yang saat ini dipilih
     */
    #[Computed]
    public function selectedOrder(): ?Order
    {
        if (!$this->selectedOrderId) return null;
        return Order::with('user')->find($this->selectedOrderId);
    }

    /**
     * Computed: pesan dari order yang dipilih
     */
    #[Computed]
    public function messages()
    {
        if (!$this->selectedOrderId) return collect();

        return Message::query()
            ->with('sender')
            ->where('order_id', $this->selectedOrderId)
            ->oldest()
            ->get();
    }

    /**
     * Pilih order dari sidebar
     */
    public function selectOrder(int $orderId): void
    {
        $this->selectedOrderId = $orderId;
        $this->newMessage = '';
        $this->markAsRead();
    }

    /**
     * Tandai semua pesan dari pihak lain sebagai sudah dibaca
     */
    public function markAsRead(): void
    {
        if (!$this->selectedOrderId) return;

        Message::where('order_id', $this->selectedOrderId)
            ->where('sender_id', '!=', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    /**
     * Kirim pesan baru
     */
    public function sendMessage(): void
    {
        // Gunakan Validator::make() secara langsung agar tidak konflik
        // dengan #[Computed] properties yang mengembalikan Collection/Model.
        // $this->validate() di Livewire 4 memanggil array_merge() pada
        // semua data komponen termasuk computed properties (yang bukan array).
        $validator = Validator::make(
            ['newMessage' => $this->newMessage],
            ['newMessage' => 'required|string|max:5000'],
            ['newMessage.required' => 'Pesan tidak boleh kosong.']
        );

        if ($validator->fails()) {
            $this->addError('newMessage', $validator->errors()->first('newMessage'));
            return;
        }

        if (!$this->selectedOrderId) return;

        Message::create([
            'order_id' => $this->selectedOrderId,
            'sender_id' => auth()->id(),
            'message' => trim($this->newMessage),
            'is_read' => false,
        ]);

        $this->reset('newMessage');
        $this->resetErrorBag('newMessage');

        // Dispatch browser event untuk scroll ke bawah
        $this->dispatch('message-sent');
    }
};
?>

<div class="space-y-4">

    {{-- Page Header --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Inbox</h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Manage client conversations and project discussions.
            </p>
        </div>
    </div>

    {{-- Main Layout: Sidebar + Chat --}}
    <div class="grid h-[calc(100vh-9rem)] gap-4 lg:grid-cols-[360px_1fr]">

        {{-- ======= SIDEBAR ======= --}}
        <div class="flex flex-col overflow-hidden rounded-[20px] border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

            {{-- Sidebar Header --}}
            <div class="border-b border-zinc-200 p-4 dark:border-zinc-800">
                <p class="mb-3 text-sm text-zinc-500">Active conversations</p>
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    icon="magnifying-glass"
                    placeholder="Search client or order..."
                    size="sm"
                />
            </div>

            {{-- Conversation List --}}
            <div class="flex-1 overflow-y-auto">

                @forelse($this->conversationList as $conv)
                    @php
                        $lastMsg = $conv->messages->first();
                        $isSelected = $this->selectedOrderId === $conv->id;
                    @endphp

                    <button
                        wire:key="conv-{{ $conv->id }}"
                        wire:click="selectOrder({{ $conv->id }})"
                        class="
                            flex w-full items-start gap-3 border-b border-zinc-100 p-4 text-left
                            transition-colors duration-150 hover:bg-zinc-50
                            dark:border-zinc-800 dark:hover:bg-zinc-800/40
                            {{ $isSelected ? 'bg-zinc-100 dark:bg-zinc-800' : '' }}
                        "
                    >
                        {{-- Avatar --}}
                        <div class="relative shrink-0">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-zinc-200 text-sm font-semibold dark:bg-zinc-700">
                                {{ str($conv->user->name)->substr(0, 2)->upper() }}
                            </div>
                            {{-- Online indicator (dekoratif) --}}
                            <div class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white bg-emerald-500 dark:border-zinc-900"></div>
                        </div>

                        {{-- Content --}}
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <h3 class="truncate text-sm font-semibold">{{ $conv->user->name }}</h3>
                                <span class="shrink-0 text-xs text-zinc-400">
                                    {{ $conv->updated_at->diffForHumans(null, true) }}
                                </span>
                            </div>

                            <p class="mt-0.5 truncate text-xs text-zinc-500">
                                {{ $lastMsg?->message ?? 'No messages yet' }}
                            </p>

                            <div class="mt-2 flex items-center justify-between gap-2">
                                <flux:badge size="sm" color="{{ match($conv->status) {
                                    'pending' => 'yellow',
                                    'review' => 'zinc',
                                    'approved' => 'green',
                                    'in_progress' => 'blue',
                                    'revision' => 'orange',
                                    'done' => 'emerald',
                                    default => 'zinc',
                                } }}">
                                    {{ str($conv->status)->replace('_', ' ')->title() }}
                                </flux:badge>

                                @if($conv->unread_count > 0)
                                    <div class="flex h-5 w-5 items-center justify-center rounded-full bg-black text-[10px] font-bold text-white dark:bg-white dark:text-black">
                                        {{ $conv->unread_count > 9 ? '9+' : $conv->unread_count }}
                                    </div>
                                @endif
                            </div>
                        </div>

                    </button>

                @empty
                    <div class="flex flex-col items-center justify-center gap-3 px-4 py-16 text-center">
                        <flux:icon name="chat-bubble-left-right" class="size-10 text-zinc-300" />
                        <p class="text-sm text-zinc-400">No conversations found</p>
                    </div>
                @endforelse

            </div>
        </div>

        {{-- ======= CHAT AREA ======= --}}
        <div class="flex h-full flex-col overflow-hidden rounded-[20px] border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

            @if($this->selectedOrder)

                {{-- Chat Header --}}
                <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-800">
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-zinc-200 text-sm font-semibold dark:bg-zinc-700">
                                {{ str($this->selectedOrder->user->name)->substr(0, 2)->upper() }}
                            </div>
                            <div class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white bg-emerald-500 dark:border-zinc-900"></div>
                        </div>
                        <div>
                            <h2 class="font-semibold">{{ $this->selectedOrder->user->name }}</h2>
                            <p class="text-sm text-zinc-400">{{ $this->selectedOrder->title }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <flux:badge color="{{ match($this->selectedOrder->status) {
                            'pending' => 'yellow',
                            'review' => 'zinc',
                            'approved' => 'green',
                            'in_progress' => 'blue',
                            'revision' => 'orange',
                            'done' => 'emerald',
                            default => 'zinc',
                        } }}">
                            {{ str($this->selectedOrder->status)->replace('_', ' ')->title() }}
                        </flux:badge>
                        <flux:button variant="ghost" icon="ellipsis-horizontal" size="sm" />
                    </div>
                </div>

                {{-- Messages Area --}}
                <div
                    id="messages-container"
                    class="flex-1 overflow-y-auto bg-zinc-50/50 px-6 py-6 dark:bg-zinc-950/20"
                    wire:poll.3000ms="$refresh"
                    x-data
                    x-on:message-sent.window="$el.scrollTop = $el.scrollHeight"
                    x-init="$el.scrollTop = $el.scrollHeight"
                >
                    <div class="mx-auto max-w-3xl space-y-4">

                        @forelse($this->messages as $message)
                            @php
                                $isMine = $message->sender_id === auth()->id();
                            @endphp

                            <div
                                wire:key="msg-{{ $message->id }}"
                                class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}"
                            >
                                <div class="flex max-w-[70%] items-end gap-2 {{ $isMine ? 'flex-row-reverse' : '' }}">

                                    {{-- Avatar (hanya untuk pesan orang lain) --}}
                                    @unless($isMine)
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-zinc-200 text-xs font-semibold dark:bg-zinc-700">
                                            {{ str($message->sender->name)->substr(0, 2)->upper() }}
                                        </div>
                                    @endunless

                                    {{-- Bubble --}}
                                    <div class="
                                        rounded-2xl px-4 py-2.5 shadow-sm text-sm leading-relaxed
                                        {{ $isMine
                                            ? 'rounded-br-sm bg-zinc-900 text-white dark:bg-white dark:text-zinc-900'
                                            : 'rounded-bl-sm border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100'
                                        }}
                                    ">
                                        <p class="break-words whitespace-pre-wrap">{{ $message->message }}</p>
                                        <div class="mt-1 flex {{ $isMine ? 'justify-end' : 'justify-start' }} text-[10px] {{ $isMine ? 'text-zinc-400 dark:text-zinc-500' : 'text-zinc-400' }}">
                                            {{ $message->created_at->format('H:i') }}
                                            @if($isMine)
                                                &nbsp;{{ $message->is_read ? '✓✓' : '✓' }}
                                            @endif
                                        </div>
                                    </div>

                                </div>
                            </div>

                        @empty
                            <div class="flex flex-col items-center justify-center gap-3 py-16 text-center">
                                <flux:icon name="chat-bubble-oval-left-ellipsis" class="size-12 text-zinc-300" />
                                <div>
                                    <p class="font-medium text-zinc-500">No messages yet</p>
                                    <p class="text-sm text-zinc-400">Start the conversation below</p>
                                </div>
                            </div>
                        @endforelse

                    </div>
                </div>

                {{-- Input Area --}}
                <div class="border-t border-zinc-200 bg-white px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center gap-3">
                        {{-- Attachment button (UI only, belum fungsional) --}}
                        <flux:button
                            variant="ghost"
                            icon="paper-clip"
                            size="sm"
                            class="shrink-0"
                        />

                        {{-- Message Input --}}
                        <div class="flex-1">
                            <flux:input
                                wire:model="newMessage"
                                wire:keydown.enter.prevent="sendMessage"
                                placeholder="Type a message..."
                                class="rounded-full"
                            />
                        </div>

                        {{-- Send Button --}}
                        <flux:button
                            wire:click="sendMessage"
                            wire:loading.attr="disabled"
                            variant="primary"
                            icon="paper-airplane"
                            size="sm"
                            class="shrink-0 rounded-full"
                        />
                    </div>
                </div>

            @else
                {{-- Empty State: Belum ada order dipilih --}}
                <div class="flex flex-1 flex-col items-center justify-center gap-4 text-center">
                    <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-zinc-100 dark:bg-zinc-800">
                        <flux:icon name="chat-bubble-left-right" class="size-10 text-zinc-400" />
                    </div>
                    <div>
                        <h3 class="font-semibold text-zinc-700 dark:text-zinc-300">Select a conversation</h3>
                        <p class="mt-1 text-sm text-zinc-400">Choose from the list on the left to start messaging</p>
                    </div>
                </div>
            @endif

        </div>

    </div>

</div>

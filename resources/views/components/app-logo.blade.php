@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="AppForge" {{ $attributes }}>
        <x-slot name="logo">
            <div class="flex aspect-square size-10 items-center justify-center rounded-xl bg-white dark:bg-white/10 shadow-sm border border-zinc-200 dark:border-white/10 overflow-hidden p-1.5">
                <x-app-logo-icon class="size-full" />
            </div>
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="AppForge" {{ $attributes }}>
        <x-slot name="logo">
            <div class="flex aspect-square size-10 items-center justify-center rounded-xl bg-white dark:bg-white/10 shadow-sm border border-zinc-200 dark:border-white/10 overflow-hidden p-1.5">
                <x-app-logo-icon class="size-full" />
            </div>
        </x-slot>
    </flux:brand>
@endif

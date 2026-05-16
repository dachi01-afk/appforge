<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        <style>
            @keyframes blob-float {
                0%, 100% { transform: translate(0, 0) scale(1); }
                33%       { transform: translate(30px, -50px) scale(1.1); }
                66%       { transform: translate(-20px, 20px) scale(0.9); }
            }
            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50%       { transform: translateY(-10px); }
            }
            .auth-blob {
                position: absolute;
                border-radius: 50%;
                filter: blur(60px);
                z-index: 0;
                animation: blob-float 20s infinite ease-in-out;
            }
        </style>
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            <div class="relative hidden h-full flex-col p-10 text-white lg:flex dark:border-e dark:border-neutral-800 bg-[#0f0f0f] overflow-hidden">
                {{-- Animated Background --}}
                <div class="auth-blob w-[600px] h-[600px] top-[-100px] right-[-100px] bg-indigo-500/20"></div>
                <div class="auth-blob w-[500px] h-[500px] bottom-[-100px] left-[-100px] bg-cyan-500/10" style="animation-delay: -5s;"></div>

                <div class="relative z-20 flex items-center gap-3">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-lg p-1.5">
                        <x-app-logo-icon class="size-full" />
                    </div>
                    <div>
                        <span class="text-xl font-bold tracking-tight">AppForge</span>
                        <span class="block text-[10px] uppercase tracking-[0.2em] text-zinc-400 font-semibold leading-none">Studio</span>
                    </div>
                </div>

                <div class="relative z-20 mt-auto">
                    <div class="mb-8 animate-[float_4s_infinite_ease-in-out]">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xs font-bold">
                            ✨ Platform #1 Pemesanan Aplikasi
                        </div>
                    </div>
                    <blockquote class="space-y-4">
                        <flux:heading size="xl" class="!text-white !leading-tight font-black">"Wujudkan ide aplikasi impianmu dengan proses yang transparan dan hasil premium."</flux:heading>
                        <footer class="text-zinc-400 font-medium">— Tim Developer AppForge</footer>
                    </blockquote>
                </div>
            </div>
            
            <div class="w-full lg:p-8">
                <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                    <a href="{{ route('home') }}" class="z-20 flex flex-col items-center gap-2 font-medium lg:hidden" wire:navigate>
                        <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center shadow-xl border border-zinc-100 p-2">
                            <x-app-logo-icon class="size-full" />
                        </div>
                        <span class="text-xl font-bold tracking-tight">AppForge</span>
                    </a>
                    {{ $slot }}
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>

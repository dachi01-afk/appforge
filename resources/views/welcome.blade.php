<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AppForge — Platform Pemesanan Pembuatan Aplikasi</title>
    <meta name="description" content="Platform terpercaya untuk memesan pembuatan aplikasi mobile dan web custom. Wujudkan ide digitalmu bersama tim developer profesional AppForge.">
    
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Base Animations */
        @keyframes blob-float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33%       { transform: translate(30px, -50px) scale(1.1); }
            66%       { transform: translate(-20px, 20px) scale(0.9); }
        }

        @keyframes fade-slide-up {
            from { opacity: 0; transform: translateY(40px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50%       { transform: translateY(-10px); }
        }

        @keyframes gradient-shift {
            0%   { background-position: 0% 50%; }
            50%  { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes shimmer {
            from { background-position: -200% center; }
            to   { background-position: 200% center; }
        }

        @keyframes line-draw {
            from { width: 0; }
            to { width: 100%; }
        }

        /* Utility Classes */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .reveal.animate-in {
            opacity: 1;
            transform: translateY(0);
        }

        .navbar-glass {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .dark .navbar-glass {
            background: rgba(15, 15, 15, 0.8);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .bg-gradient-animate {
            background: linear-gradient(-45deg, #6366f1, #4f46e5, #06b6d4, #2dd4bf);
            background-size: 400% 400%;
            animation: gradient-shift 15s ease infinite;
        }

        .blob {
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, rgba(6, 182, 212, 0) 70%);
            border-radius: 50%;
            filter: blur(60px);
            z-index: -1;
            animation: blob-float 20s infinite ease-in-out;
        }

        .btn-shimmer {
            background: linear-gradient(90deg, #6366f1, #4f46e5, #6366f1);
            background-size: 200% auto;
            transition: 0.5s;
        }

        .btn-shimmer:hover {
            background-position: right center;
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.4);
            transform: translateY(-2px);
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
            border-color: rgba(99, 102, 241, 0.2);
        }
    </style>
</head>
<body class="bg-[#fafafa] dark:bg-[#0f0f0f] text-[#111827] dark:text-[#fafafa] font-sans overflow-x-hidden">
    
    {{-- Background Decorations --}}
    <div class="blob top-[-100px] right-[-100px]"></div>
    <div class="blob bottom-[20%] left-[-150px]" style="animation-delay: -5s; width: 600px; height: 600px; background: radial-gradient(circle, rgba(6, 182, 212, 0.1) 0%, rgba(99, 102, 241, 0) 70%);"></div>

    {{-- Navbar --}}
    <nav id="navbar" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 py-6">
        <div class="max-w-7xl mx-auto px-6 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20">
                    <flux:icon name="command-line" class="size-6 text-white" />
                </div>
                <div>
                    <span class="text-xl font-bold tracking-tight">AppForge</span>
                    <span class="block text-[10px] uppercase tracking-[0.2em] text-zinc-400 font-semibold leading-none">Studio</span>
                </div>
            </div>

            <div class="hidden md:flex items-center gap-8">
                <a href="#features" class="text-sm font-medium hover:text-indigo-600 transition-colors">Fitur</a>
                <a href="#how-it-works" class="text-sm font-medium hover:text-indigo-600 transition-colors">Cara Kerja</a>
                <a href="#testimonials" class="text-sm font-medium hover:text-indigo-600 transition-colors">Testimonial</a>
                <a href="#contact" class="text-sm font-medium hover:text-indigo-600 transition-colors">Kontak</a>
            </div>

            <div class="flex items-center gap-3">
                @auth
                    <flux:button variant="ghost" size="sm" href="{{ route('dashboard') }}" wire:navigate>Dashboard</flux:button>
                @else
                    <flux:button variant="ghost" size="sm" href="{{ route('login') }}" wire:navigate>Log in</flux:button>
                    <flux:button variant="primary" size="sm" class="!bg-indigo-600 hover:!bg-indigo-700 !text-white border-none" href="#download">Download App</flux:button>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Hero Section --}}
    <section class="relative pt-40 pb-20 overflow-hidden">
        <div class="max-w-7xl mx-auto px-6 grid lg:grid-cols-2 gap-16 items-center">
            <div class="reveal">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 text-indigo-600 dark:text-indigo-400 text-xs font-bold mb-6">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                    </span>
                    ✨ Platform #1 Pemesanan Pembuatan Aplikasi
                </div>
                <h1 class="text-5xl lg:text-7xl font-black leading-[1.1] tracking-tight mb-6">
                    Wujudkan Ide Digitalmu <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-cyan-500">Bersama AppForge</span>
                </h1>
                <p class="text-lg text-zinc-500 dark:text-zinc-400 mb-10 max-w-lg leading-relaxed">
                    Kami membantu startup dan bisnis membangun aplikasi mobile & web premium dengan proses yang transparan, terstruktur, dan tepat waktu.
                </p>
                <div class="flex flex-wrap gap-4">
                    <flux:button size="base" class="!bg-indigo-600 hover:!bg-indigo-700 !text-white !px-8 h-14 text-base font-bold shadow-xl shadow-indigo-500/25 border-none" href="#download">Download Sekarang ↓</flux:button>
                    <flux:button size="base" variant="ghost" class="h-14 !px-8 text-base font-semibold" href="#how-it-works">Lihat Cara Kerja</flux:button>
                </div>

                <div class="mt-16 flex items-center gap-8">
                    <div>
                        <div class="text-3xl font-black text-indigo-600 count-up" data-target="500" data-suffix="+">0</div>
                        <div class="text-xs font-bold text-zinc-400 uppercase tracking-widest mt-1">Project Selesai</div>
                    </div>
                    <div class="w-px h-10 bg-zinc-200 dark:bg-zinc-800"></div>
                    <div>
                        <div class="text-3xl font-black text-indigo-600 count-up" data-target="50" data-suffix="+">0</div>
                        <div class="text-xs font-bold text-zinc-400 uppercase tracking-widest mt-1">Happy Clients</div>
                    </div>
                    <div class="w-px h-10 bg-zinc-200 dark:bg-zinc-800"></div>
                    <div>
                        <div class="text-3xl font-black text-indigo-600 count-up" data-target="99" data-suffix="%">0</div>
                        <div class="text-xs font-bold text-zinc-400 uppercase tracking-widest mt-1">Kepuasan</div>
                    </div>
                </div>
            </div>

            <div class="relative reveal" style="transition-delay: 0.2s;">
                <div class="relative z-10 bg-white dark:bg-zinc-900 rounded-3xl p-4 shadow-2xl border border-zinc-100 dark:border-zinc-800">
                    <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&q=80&w=2426" alt="Dashboard Preview" class="rounded-2xl shadow-inner">
                </div>
                
                {{-- Floating Badges --}}
                <div class="absolute -top-6 -right-6 z-20 bg-emerald-500 text-white px-4 py-3 rounded-2xl shadow-xl flex items-center gap-3 animate-[float_4s_infinite_ease-in-out]">
                    <div class="bg-white/20 p-2 rounded-lg"><flux:icon name="check" class="size-5" /></div>
                    <div>
                        <div class="text-[10px] font-bold uppercase opacity-80 leading-none">Status</div>
                        <div class="text-sm font-bold">Project Berhasil Launch!</div>
                    </div>
                </div>

                <div class="absolute -bottom-10 -left-10 z-20 bg-indigo-600 text-white px-5 py-4 rounded-2xl shadow-xl flex items-center gap-4 animate-[float_5s_infinite_ease-in-out_-2s]">
                    <div class="flex -space-x-3">
                        <div class="w-10 h-10 rounded-full border-4 border-indigo-600 bg-zinc-200 overflow-hidden"><img src="https://ui-avatars.com/api/?name=Admin+A&background=random" alt="User"></div>
                        <div class="w-10 h-10 rounded-full border-4 border-indigo-600 bg-zinc-300 overflow-hidden"><img src="https://ui-avatars.com/api/?name=User+B&background=random" alt="User"></div>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold uppercase opacity-80 leading-none">Collaboration</div>
                        <div class="text-sm font-bold">5+ Devs Online</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Features Section --}}
    <section id="features" class="py-32 bg-white dark:bg-black/20">
        <div class="max-w-7xl mx-auto px-6 text-center mb-20">
            <h2 class="text-xs font-black uppercase tracking-[0.3em] text-indigo-600 mb-4 reveal">Layanan Unggulan</h2>
            <p class="text-4xl font-black tracking-tight mb-4 reveal" style="transition-delay: 0.1s;">Solusi Digital Lengkap untuk Bisnis Anda</p>
            <p class="text-zinc-500 max-w-2xl mx-auto reveal" style="transition-delay: 0.2s;">Kami menggabungkan keahlian desain dan teknologi untuk menciptakan pengalaman pengguna yang luar biasa.</p>
        </div>

        <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            @php
                $features = [
                    ['icon' => '📱', 'title' => 'Aplikasi Mobile', 'desc' => 'Android & iOS native dengan performa tinggi menggunakan Flutter terbaru.'],
                    ['icon' => '🌐', 'title' => 'Aplikasi Web', 'desc' => 'Web app modern, responsif, dan SEO-friendly dengan Laravel & Vue/React.'],
                    ['icon' => '📊', 'title' => 'Dashboard Admin', 'desc' => 'Panel manajemen data kustom untuk kontrol penuh operasional bisnis Anda.'],
                    ['icon' => '🔄', 'title' => 'Tracking Real-time', 'desc' => 'Monitor progress pengerjaan proyek Anda kapan saja melalui dashboard khusus.'],
                    ['icon' => '💬', 'title' => 'Komunikasi Langsung', 'desc' => 'Hubungi tim developer kami secara langsung melalui fitur chat terintegrasi.'],
                    ['icon' => '✅', 'title' => 'Garansi Revisi', 'desc' => 'Kami memastikan hasil akhir sesuai dengan ekspektasi dan kebutuhan bisnis Anda.'],
                ];
            @endphp

            @foreach($features as $i => $feature)
                <div class="reveal feature-card p-10 bg-zinc-50 dark:bg-zinc-900/50 rounded-3xl border border-zinc-100 dark:border-zinc-800 transition-all duration-500 group" style="transition-delay: {{ $i * 0.1 }}s;">
                    <div class="text-4xl mb-6 group-hover:scale-110 transition-transform duration-300 inline-block">{{ $feature['icon'] }}</div>
                    <h3 class="text-xl font-bold mb-3">{{ $feature['title'] }}</h3>
                    <p class="text-zinc-500 dark:text-zinc-400 leading-relaxed">{{ $feature['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- How It Works --}}
    <section id="how-it-works" class="py-32 overflow-hidden">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-24">
                <h2 class="text-xs font-black uppercase tracking-[0.3em] text-indigo-600 mb-4 reveal">Alur Kerja</h2>
                <p class="text-4xl font-black tracking-tight mb-4 reveal" style="transition-delay: 0.1s;">Proses Transparan dari Awal Hingga Launch</p>
            </div>

            <div class="relative">
                {{-- Progress Line --}}
                <div class="absolute top-1/2 left-0 w-full h-0.5 bg-zinc-200 dark:bg-zinc-800 -translate-y-1/2 hidden lg:block reveal-line"></div>
                
                <div class="grid lg:grid-cols-4 gap-12">
                    @php
                        $steps = [
                            ['num' => '01', 'title' => 'Daftar Akun', 'desc' => 'Buat akun Anda dalam hitungan detik untuk mengakses dashboard.'],
                            ['num' => '02', 'title' => 'Isi Form Order', 'desc' => 'Berikan detail ide proyek, fitur, dan preferensi desain Anda.'],
                            ['num' => '03', 'title' => 'Review & Kerjakan', 'desc' => 'Admin me-review order dan tim developer mulai membangun aplikasi.'],
                            ['num' => '04', 'title' => 'Terima Aplikasi', 'desc' => 'Aplikasi siap digunakan dan tim kami akan membantu proses deploy.'],
                        ];
                    @endphp

                    @foreach($steps as $i => $step)
                        <div class="reveal relative z-10 bg-[#fafafa] dark:bg-[#0f0f0f] lg:text-center" style="transition-delay: {{ $i * 0.15 }}s;">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 shadow-xl text-indigo-600 font-black text-xl mb-8 group-hover:rotate-12 transition-transform">
                                {{ $step['num'] }}
                            </div>
                            <h3 class="text-xl font-bold mb-4">{{ $step['title'] }}</h3>
                            <p class="text-zinc-500 dark:text-zinc-400 text-sm leading-relaxed">{{ $step['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Testimonials --}}
    <section id="testimonials" class="py-32 bg-indigo-600 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full bg-gradient-animate opacity-40"></div>
        
        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <div class="text-center mb-20">
                <h2 class="text-xs font-black uppercase tracking-[0.3em] text-indigo-200 mb-4">Social Proof</h2>
                <p class="text-4xl font-black tracking-tight text-white reveal">Apa Kata Mereka Tentang Kami?</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                @php
                    $testimonials = [
                        ['name' => 'Budi Santoso', 'role' => 'Owner Laundry Kilat', 'text' => 'Aplikasi laundry kami selesai tepat waktu dan sesuai ekspektasi. User interface-nya sangat intuitif!'],
                        ['name' => 'Siti Rahayu', 'role' => 'CEO StartupKu', 'text' => 'Tim AppForge sangat responsif dan profesional. Sangat direkomendasikan untuk membangun MVP startup.'],
                        ['name' => 'Ahmad Fauzi', 'role' => 'Founder UMKM Digital', 'text' => 'Progress tracking-nya membantu kami memantau perkembangan aplikasi kami tanpa perlu tanya-tanya terus.'],
                    ];
                @endphp

                @foreach($testimonials as $i => $t)
                    <div class="reveal p-8 bg-white/10 backdrop-blur-md rounded-3xl border border-white/20 text-white" style="transition-delay: {{ $i * 0.1 }}s;">
                        <div class="flex gap-1 text-yellow-400 mb-6">
                            @for($j=0; $j<5; $j++) <flux:icon name="star" class="size-4" variant="solid" /> @endfor
                        </div>
                        <p class="text-lg font-medium leading-relaxed mb-8 italic">"{{ $t['text'] }}"</p>
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-indigo-400 overflow-hidden flex items-center justify-center font-bold text-white border-2 border-white/30">
                                {{ substr($t['name'], 0, 1) }}
                            </div>
                            <div>
                                <div class="font-bold">{{ $t['name'] }}</div>
                                <div class="text-xs opacity-70">{{ $t['role'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA Banner --}}
    <section class="py-24">
        <div class="max-w-7xl mx-auto px-6">
            <div class="reveal p-12 lg:p-20 bg-zinc-900 rounded-[40px] text-center relative overflow-hidden shadow-2xl">
                {{-- Background Deco --}}
                <div class="absolute top-0 right-0 w-1/2 h-full bg-gradient-to-l from-indigo-500/20 to-transparent"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-cyan-500/10 rounded-full filter blur-[100px]"></div>

                <div class="relative z-10">
                    <h2 class="text-4xl lg:text-5xl font-black text-white mb-8">Siap Mewujudkan Aplikasi Impianmu?</h2>
                    <p class="text-zinc-400 max-w-xl mx-auto mb-12 text-lg">
                        Bergabunglah dengan puluhan bisnis lainnya yang telah sukses melakukan transformasi digital bersama AppForge.
                    </p>
                    <div class="flex flex-wrap justify-center gap-4">
                        <flux:button size="base" class="!bg-indigo-600 hover:!bg-indigo-700 !text-white !px-10 h-16 text-lg font-bold shadow-2xl shadow-indigo-500/40 border-none btn-shimmer" href="#download">Download Aplikasi Sekarang</flux:button>
                        <flux:button size="base" variant="ghost" class="h-16 !px-10 text-lg font-semibold !text-white hover:!bg-white/5" href="#contact">Konsultasi Dulu</flux:button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Download App Section --}}
    <section id="download" class="py-24 bg-white dark:bg-zinc-900/50">
        <div class="max-w-7xl mx-auto px-6 grid lg:grid-cols-2 gap-16 items-center">
            <div class="reveal">
                <h2 class="text-4xl font-black tracking-tight mb-6">Pesan & Pantau Aplikasi Anda dari Genggaman</h2>
                <p class="text-zinc-500 dark:text-zinc-400 text-lg mb-10 leading-relaxed">
                    Nikmati kemudahan memesan aplikasi, berkonsultasi dengan developer, hingga memantau progres pengerjaan secara real-time langsung dari smartphone Anda.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="#" class="flex items-center gap-3 bg-black text-white px-6 py-3 rounded-2xl hover:scale-105 transition-transform border border-zinc-800">
                        <flux:icon name="command-line" class="size-8" />
                        <div class="text-left">
                            <div class="text-[10px] uppercase font-bold opacity-60 leading-none">Get it on</div>
                            <div class="text-xl font-bold leading-tight">Google Play</div>
                        </div>
                    </a>
                    <a href="#" class="flex items-center gap-3 bg-black text-white px-6 py-3 rounded-2xl hover:scale-105 transition-transform border border-zinc-800">
                        <flux:icon name="at-symbol" class="size-8" />
                        <div class="text-left">
                            <div class="text-[10px] uppercase font-bold opacity-60 leading-none">Download on the</div>
                            <div class="text-xl font-bold leading-tight">App Store</div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="reveal relative flex justify-center" style="transition-delay: 0.2s;">
                <div class="w-[280px] h-[580px] bg-zinc-900 rounded-[3rem] p-3 shadow-2xl border-4 border-zinc-800 relative">
                    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-32 h-6 bg-zinc-800 rounded-b-2xl z-10"></div>
                    <div class="w-full h-full bg-indigo-600 rounded-[2.5rem] overflow-hidden p-6 flex flex-col justify-center items-center text-white text-center">
                        <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mb-6">
                            <flux:icon name="command-line" class="size-10" />
                        </div>
                        <h3 class="text-2xl font-bold mb-2">AppForge</h3>
                        <p class="text-sm opacity-80">Ready to build your dream app?</p>
                    </div>
                </div>
                {{-- Decorative blobs --}}
                <div class="absolute -z-10 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-indigo-500/20 rounded-full blur-[80px]"></div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="py-20 border-t border-zinc-100 dark:border-zinc-800">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid lg:grid-cols-4 gap-12 mb-16">
                <div class="lg:col-span-2">
                    <div class="flex items-center gap-2 mb-6">
                        <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
                            <flux:icon name="command-line" class="size-5 text-white" />
                        </div>
                        <span class="text-lg font-bold tracking-tight">AppForge</span>
                    </div>
                    <p class="text-zinc-500 max-w-sm mb-8">
                        Platform pemesanan pembuatan aplikasi mobile & web terpercaya. Kami menghadirkan kualitas premium dengan kecepatan startup.
                    </p>
                    <div class="flex gap-4">
                        @foreach(['twitter', 'facebook', 'instagram', 'github'] as $social)
                            <a href="#" class="w-10 h-10 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-500 hover:bg-indigo-600 hover:text-white transition-all">
                                <flux:icon name="at-symbol" class="size-5" />
                            </a>
                        @endforeach
                    </div>
                </div>
                
                <div>
                    <h4 class="font-bold mb-6">Perusahaan</h4>
                    <ul class="space-y-4 text-sm text-zinc-500">
                        <li><a href="#" class="hover:text-indigo-600 transition-colors">Tentang Kami</a></li>
                        <li><a href="#" class="hover:text-indigo-600 transition-colors">Karir</a></li>
                        <li><a href="#" class="hover:text-indigo-600 transition-colors">Blog</a></li>
                        <li><a href="#" class="hover:text-indigo-600 transition-colors">Kontak</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold mb-6">Legal</h4>
                    <ul class="space-y-4 text-sm text-zinc-500">
                        <li><a href="#" class="hover:text-indigo-600 transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-indigo-600 transition-colors">Terms of Service</a></li>
                        <li><a href="#" class="hover:text-indigo-600 transition-colors">Cookie Policy</a></li>
                    </ul>
                </div>
            </div>

            <div class="pt-8 border-t border-zinc-100 dark:border-zinc-800 flex flex-col md:flex-row justify-between items-center gap-4 text-xs font-medium text-zinc-400">
                <p>© 2026 AppForge. All rights reserved.</p>
                <div class="flex items-center gap-6">
                    <span>Dibuat dengan ❤️ oleh Tim AppForge</span>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Navbar Scroll Effect
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-glass');
                navbar.classList.remove('py-6');
                navbar.classList.add('py-4');
            } else {
                navbar.classList.remove('navbar-glass');
                navbar.classList.add('py-6');
                navbar.classList.remove('py-4');
            }
        });

        // Intersection Observer for Reveal
        const observerOptions = {
            threshold: 0.1
        };

        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                    
                    // Specific logic for counters
                    const counters = entry.target.querySelectorAll('.count-up');
                    counters.forEach(counter => {
                        if (!counter.classList.contains('animated')) {
                            animateCounter(counter);
                            counter.classList.add('animated');
                        }
                    });
                }
            });
        }, observerOptions);

        document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

        // Counter Animation Function
        function animateCounter(el) {
            const target = parseInt(el.getAttribute('data-target'));
            const suffix = el.getAttribute('data-suffix') || '';
            const duration = 2000;
            const startTime = performance.now();

            function update(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                // Ease out expo
                const easeValue = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
                const currentCount = Math.floor(easeValue * target);
                
                el.textContent = currentCount + suffix;

                if (progress < 1) {
                    requestAnimationFrame(update);
                }
            }

            requestAnimationFrame(update);
        }
    </script>
</body>
</html>

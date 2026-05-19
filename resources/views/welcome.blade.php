<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>AlmaConnect — Alumni Network for Our Institute Graduates</title>
    <meta name="description" content="Reconnect with classmates, mentor students, find jobs, attend events, and give back. The official alumni network built for institute graduates.">
    <link rel="canonical" href="{{ url('/') }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="AlmaConnect — Alumni Network">
    <meta property="og:description" content="Reconnect with classmates, mentor students, and give back. The alumni network for our institute graduates.">
    <meta property="og:image" content="{{ url('/og-image.jpg') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="AlmaConnect — Alumni Network">
    <meta name="twitter:description" content="Reconnect, mentor, give back. The alumni network for institute graduates.">
    <meta name="twitter:image" content="{{ url('/og-image.jpg') }}">

    {{-- PWA + Favicons --}}
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎓</text></svg>">

    @vite(['resources/css/app.css', 'resources/js/landing.js'])

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>

    <style>
        @keyframes mesh-shift {
            0%   { background-position: 0% 50%; }
            50%  { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .hero-mesh {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 35%, #ec4899 65%, #4f46e5 100%);
            background-size: 400% 400%;
            animation: mesh-shift 20s linear infinite;
        }
        @media (prefers-reduced-motion: reduce) {
            .hero-mesh { animation: none; }
            [data-hero-word], [data-hero-sub], [data-hero-cta], [data-hero-stats] {
                opacity: 1 !important; transform: none !important;
            }
        }
        [data-hero-word], [data-hero-sub], [data-hero-cta], [data-hero-stats] {
            opacity: 0;
        }
        html { scroll-behavior: smooth; }
        @media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } }
    </style>
</head>

<body class="antialiased text-gray-800 bg-white" x-data="{ feedbackOpen: false }">

{{-- ════════════════════════════════════════════════════════ NAV ══ --}}
<header
    x-data="{ mobileOpen: false, solid: false }"
    x-init="window.addEventListener('scroll', () => solid = window.scrollY > 100)"
    :class="solid ? 'bg-white/95 shadow-sm backdrop-blur' : 'bg-transparent'"
    class="fixed inset-x-0 top-0 z-50 transition-all duration-200"
>
    <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8" aria-label="Main navigation">
        <a href="/" class="flex items-center gap-2 text-xl font-bold text-white" :class="solid ? 'text-gray-900' : 'text-white'">
            🎓 <span>Alma<span class="text-indigo-400" :class="solid ? 'text-indigo-600' : 'text-indigo-400'">Connect</span></span>
        </a>

        {{-- Desktop nav --}}
        <div class="hidden items-center gap-8 text-sm font-medium md:flex"
             :class="solid ? 'text-gray-700' : 'text-white/90'">
            <a href="#features" class="hover:text-indigo-500 transition">Features</a>
            <a href="#stories" class="hover:text-indigo-500 transition">Stories</a>
            <a href="#faq" class="hover:text-indigo-500 transition">FAQ</a>
            <a href="{{ route('login') }}" class="rounded-lg border px-4 py-1.5 transition"
               :class="solid ? 'border-gray-300 hover:bg-gray-50' : 'border-white/40 hover:border-white'">Log in</a>
            <a href="{{ route('register') }}"
               class="rounded-lg bg-indigo-600 px-4 py-1.5 text-white shadow-sm transition hover:bg-indigo-700">
                Get started
            </a>
        </div>

        {{-- Hamburger --}}
        <button
            class="rounded-md p-2 md:hidden"
            :class="solid ? 'text-gray-700' : 'text-white'"
            @click="mobileOpen = true"
            aria-label="Open navigation menu"
            :aria-expanded="mobileOpen"
        >
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
    </nav>

    {{-- Mobile drawer --}}
    <div
        x-show="mobileOpen"
        x-transition:enter="transition duration-300 ease-out"
        x-transition:enter-start="translate-x-full opacity-0"
        x-transition:enter-end="translate-x-0 opacity-100"
        x-transition:leave="transition duration-200 ease-in"
        x-transition:leave-start="translate-x-0 opacity-100"
        x-transition:leave-end="translate-x-full opacity-0"
        class="fixed inset-y-0 right-0 z-50 w-72 bg-white shadow-2xl"
        @click.outside="mobileOpen = false"
        role="dialog"
        aria-label="Mobile navigation"
        style="display: none"
    >
        <div class="flex h-16 items-center justify-between px-5 border-b border-gray-100">
            <span class="text-lg font-bold text-gray-900">Menu</span>
            <button @click="mobileOpen = false" class="rounded-md p-1 text-gray-500 hover:text-gray-900" aria-label="Close menu">
                &times;
            </button>
        </div>
        <div class="flex flex-col gap-1 p-4 text-sm font-medium text-gray-700">
            <a href="#features" class="rounded-md px-3 py-2.5 hover:bg-gray-50" @click="mobileOpen=false">Features</a>
            <a href="#stories" class="rounded-md px-3 py-2.5 hover:bg-gray-50" @click="mobileOpen=false">Stories</a>
            <a href="#faq" class="rounded-md px-3 py-2.5 hover:bg-gray-50" @click="mobileOpen=false">FAQ</a>
            <hr class="my-2 border-gray-100">
            <a href="{{ route('login') }}" class="rounded-md px-3 py-2.5 hover:bg-gray-50">Log in</a>
            <a href="{{ route('register') }}" class="rounded-lg bg-indigo-600 px-3 py-2.5 text-center text-white hover:bg-indigo-700">Get started</a>
        </div>
    </div>
    <div x-show="mobileOpen" class="fixed inset-0 z-40 bg-black/40 md:hidden" @click="mobileOpen=false" style="display:none"></div>
</header>

{{-- ══════════════════════════════════════════════════════════ HERO ══ --}}
<section class="hero-mesh relative min-h-screen overflow-hidden px-4 text-white">
    <div class="absolute inset-0 bg-black/15"></div>
    <div class="relative z-10 mx-auto flex min-h-screen max-w-7xl items-center px-4 py-32 sm:px-6 lg:px-8">
        <div class="grid w-full grid-cols-1 items-center gap-12 lg:grid-cols-2">

            {{-- ── Left: copy + CTAs ── --}}
            <div class="text-center lg:text-left">
                <h1 class="text-4xl font-bold tracking-tight leading-tight md:text-5xl lg:text-6xl">
                    @php $words = explode(' ', 'The alumni network for institute graduates.'); @endphp
                    @foreach($words as $word)
                        <span data-hero-word class="inline-block mr-2">{{ $word }}</span>
                    @endforeach
                </h1>

                <p data-hero-sub class="mx-auto mt-6 max-w-xl text-lg leading-relaxed text-white/85 lg:mx-0 md:text-xl">
                    Reconnect with classmates. Mentor the next batch. Discover opportunities, attend reunions, and give back to the place that shaped you.
                </p>

                <div class="mt-10 flex flex-wrap items-center justify-center gap-4 lg:justify-start">
                    <a data-hero-cta href="{{ route('register') }}"
                       class="rounded-xl bg-white px-8 py-3.5 text-base font-semibold text-indigo-700 shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-white/50">
                        Get started — it's free
                    </a>
                    <a data-hero-cta href="{{ route('login') }}"
                       class="rounded-xl border border-white/40 px-8 py-3.5 text-base font-semibold text-white transition hover:bg-white/10 focus:outline-none focus:ring-4 focus:ring-white/30">
                        I have an account →
                    </a>
                </div>

                <p data-hero-stats class="mt-10 text-sm text-white/70">
                    🟢 {{ number_format($stats['alumni']) }} alumni &nbsp;·&nbsp; {{ $stats['events'] }}+ events &nbsp;·&nbsp; {{ $stats['mentors'] }}+ mentors
                </p>
            </div>

            {{-- ── Right: dashboard preview card ── --}}
            <div data-hero-cta class="hidden lg:flex lg:justify-center lg:items-center">
                <div class="relative w-full max-w-sm">

                    {{-- Main card --}}
                    <div class="rounded-2xl border border-white/20 bg-white/10 p-6 shadow-2xl backdrop-blur-sm">
                        {{-- Card header --}}
                        <div class="mb-5 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20 text-xl">🎓</div>
                                <div>
                                    <p class="text-sm font-semibold">Alumni Dashboard</p>
                                    <p class="text-xs text-white/60">AlmaConnect</p>
                                </div>
                            </div>
                            <span class="rounded-full bg-emerald-400/20 px-2.5 py-1 text-xs font-semibold text-emerald-300">● Live</span>
                        </div>

                        {{-- Stats row --}}
                        <div class="mb-5 grid grid-cols-3 gap-3">
                            <div class="rounded-xl bg-white/10 p-3 text-center">
                                <p class="text-xl font-bold">{{ number_format($stats['alumni']) }}</p>
                                <p class="mt-0.5 text-xs text-white/60">Alumni</p>
                            </div>
                            <div class="rounded-xl bg-white/10 p-3 text-center">
                                <p class="text-xl font-bold">{{ $stats['events'] }}</p>
                                <p class="mt-0.5 text-xs text-white/60">Events</p>
                            </div>
                            <div class="rounded-xl bg-white/10 p-3 text-center">
                                <p class="text-xl font-bold">{{ $stats['mentors'] }}</p>
                                <p class="mt-0.5 text-xs text-white/60">Mentors</p>
                            </div>
                        </div>

                        {{-- Recent members --}}
                        <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-white/50">Recent Members</p>
                        <div class="space-y-2.5">
                            @foreach([
                                ['name'=>'Rahul Sharma',  'meta'=>'CSE · Class of 2022', 'color'=>'bg-indigo-400'],
                                ['name'=>'Anjali Patel',  'meta'=>'ECE · Class of 2021', 'color'=>'bg-pink-400'],
                                ['name'=>'Nikhil Kumar',  'meta'=>'ME  · Class of 2020', 'color'=>'bg-violet-400'],
                            ] as $m)
                            <div class="flex items-center gap-3 rounded-lg bg-white/5 px-3 py-2">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $m['color'] }} text-xs font-bold text-white">
                                    {{ strtoupper(substr($m['name'], 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium">{{ $m['name'] }}</p>
                                    <p class="text-xs text-white/55">{{ $m['meta'] }}</p>
                                </div>
                                <span class="ml-auto shrink-0 rounded-full bg-white/10 px-2 py-0.5 text-xs text-white/70">View</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Floating badge: new event --}}
                    <div class="absolute -right-6 -top-5 flex items-center gap-2 rounded-xl border border-white/20 bg-white/15 px-3 py-2 shadow-lg backdrop-blur-sm">
                        <span class="text-base">📅</span>
                        <div>
                            <p class="text-xs font-semibold">Reunion 2026</p>
                            <p class="text-xs text-white/60">12 going</p>
                        </div>
                    </div>

                    {{-- Floating badge: job --}}
                    <div class="absolute -bottom-5 -left-6 flex items-center gap-2 rounded-xl border border-white/20 bg-white/15 px-3 py-2 shadow-lg backdrop-blur-sm">
                        <span class="text-base">💼</span>
                        <div>
                            <p class="text-xs font-semibold">New Job Posted</p>
                            <p class="text-xs text-white/60">Senior Engineer</p>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce text-white/50">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════ STATS ══ --}}
@php
    if (!function_exists('fmtStat')) {
        function fmtStat($n, $fmt = 'number') {
            if ($fmt === 'inr') {
                if ($n >= 10000000) return '₹' . round($n/10000000, 1) . ' Cr';
                if ($n >= 100000)   return '₹' . round($n/100000, 0) . ' L';
                return '₹' . number_format($n, 0);
            }
            if ($n >= 1000) return number_format(round($n / 100) * 100);
            return number_format($n);
        }
    }
@endphp

<section class="bg-gray-50 py-20">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-sm font-semibold uppercase tracking-widest text-indigo-600">Our community by the numbers</h2>
        <div class="mt-10 grid grid-cols-2 gap-8 md:grid-cols-4">
            @foreach([
                ['val' => $stats['alumni'],    'fmt' => 'number', 'final' => number_format($stats['alumni']),    'label' => 'Alumni approved'],
                ['val' => $stats['donations'], 'fmt' => 'inr',    'final' => fmtStat($stats['donations'],'inr'),'label' => 'Donated to institute'],
                ['val' => $stats['events'],    'fmt' => 'number', 'final' => $stats['events'],                   'label' => 'Events organised'],
                ['val' => $stats['mentors'],   'fmt' => 'number', 'final' => number_format($stats['mentors']),   'label' => 'Potential mentors'],
            ] as $s)
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <p class="text-4xl font-bold text-indigo-600"
                   data-counter="{{ $s['val'] }}"
                   data-format="{{ $s['fmt'] }}"
                   data-final="{{ $s['final'] }}">
                    {{ $s['final'] }}
                </p>
                <p class="mt-1 text-sm text-gray-500">{{ $s['label'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ════════════════════════════════════════════════════ FEATURES ══ --}}
<section id="features" class="py-24">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold tracking-tight text-gray-900 md:text-4xl">
                Everything you need to stay connected
            </h2>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-600">
                One platform for the entire alumni lifecycle — from the first reunion to lifelong mentorship.
            </p>
        </div>

        <div class="mt-16 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach([
                ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />',
                 'title' => 'Searchable directory',
                 'body'  => 'Find any alumnus in seconds — filter by batch, branch, industry, city, or skills.'],
                ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />',
                 'title' => 'Networking hub',
                 'body'  => 'Join groups, post updates, like and comment. Real conversations with the people you graduated with.'],
                ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />',
                 'title' => 'Events & reunions',
                 'body'  => 'RSVP to alumni meets. Calendar view, attendee lists, capacity-aware booking with email reminders.'],
                ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z" />',
                 'title' => 'Career opportunities',
                 'body'  => 'Alumni post jobs and internships exclusively for the network. Find opportunities others can\'t see.'],
                ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />',
                 'title' => 'Give back',
                 'body'  => 'Donate to scholarship funds, lab upgrades, and library endowments — securely via Razorpay.'],
                ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" />',
                 'title' => 'Success stories',
                 'body'  => 'Read how your peers are changing the world — then submit your own story for the world to see.'],
            ] as $f)
            <article data-feature-card
                class="group rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-lg">
                <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-50 text-gray-500 transition-colors duration-200 group-hover:bg-indigo-100 group-hover:text-indigo-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        {!! $f['icon'] !!}
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900">{{ $f['title'] }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ $f['body'] }}</p>
            </article>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════ STORIES ══ --}}
@if($stories->count())
<section id="stories" class="bg-gray-50 py-24">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div data-reveal="up" class="flex items-end justify-between">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 md:text-4xl">
                    Stories that inspire
                </h2>
                <p class="mt-2 text-lg text-gray-600">Real journeys from our alumni.</p>
            </div>
            <a href="{{ route('login') }}" class="hidden font-medium text-indigo-600 hover:text-indigo-700 md:block">
                See all stories →
            </a>
        </div>

        @php $catlabels = ['entrepreneurship'=>'Entrepreneurship','research'=>'Research','social_impact'=>'Social Impact','career'=>'Career','other'=>'Other']; @endphp

        {{-- Desktop: Alpine slider --}}
        <div
            x-data="{
                pos: 0,
                max: {{ max(0, $stories->count() - 3) }},
                prev() { if (this.pos > 0) this.pos--; },
                next() { if (this.pos < this.max) this.pos++; }
            }"
            class="mt-10 relative"
        >
            <div class="overflow-hidden">
                <div
                    class="flex gap-6 transition-transform duration-350 ease-in-out"
                    :style="'transform: translateX(calc(-' + pos + ' * (100% / 3 + 8px)))'"
                >
                    @foreach($stories as $story)
                    <a href="{{ route('login') }}"
                       class="group w-full shrink-0 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md md:w-[calc(33.33%-1rem)]"
                       title="Log in to read: {{ e($story->headline) }}"
                    >
                        <div class="relative aspect-video w-full overflow-hidden bg-gradient-to-br from-indigo-500 via-indigo-600 to-purple-600"
                            @if($story->cover_image)
                            style="background-image: url('/storage/{{ $story->cover_image }}'); background-size: cover; background-position: center;"
                            @endif
                        >
                            @if(!$story->cover_image)
                            <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 opacity-30">
                                <svg class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                                </svg>
                                <span class="text-xs font-semibold text-white">Alumni Story</span>
                            </div>
                            @endif
                        </div>
                        <div class="p-4">
                            <span class="text-xs font-semibold uppercase tracking-wide text-indigo-700">
                                {{ $catlabels[$story->category] ?? $story->category }}
                            </span>
                            <h3 class="mt-1 font-semibold text-gray-900 leading-snug line-clamp-2">
                                {{ $story->headline }}
                            </h3>
                            <p class="mt-2 text-xs text-gray-500">
                                {{ $story->featuredUser?->name }}
                                @if($story->featuredUser?->profile?->batch) · Class of {{ $story->featuredUser->profile->batch }} @endif
                            </p>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>

            @if($stories->count() > 3)
            <button
                @click="prev"
                :disabled="pos === 0"
                class="absolute -left-4 top-1/2 -translate-y-1/2 rounded-full bg-white p-2 shadow-md transition hover:bg-gray-50 disabled:opacity-30 hidden md:flex"
                aria-label="Previous stories"
            >
                <svg class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            </button>
            <button
                @click="next"
                :disabled="pos === max"
                class="absolute -right-4 top-1/2 -translate-y-1/2 rounded-full bg-white p-2 shadow-md transition hover:bg-gray-50 disabled:opacity-30 hidden md:flex"
                aria-label="Next stories"
            >
                <svg class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            </button>
            @endif
        </div>
    </div>
</section>
@endif

{{-- ══════════════════════════════════════════════════ TESTIMONIALS ══ --}}
<section class="py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <h2 data-reveal="up" class="text-center text-3xl font-bold tracking-tight text-gray-900 md:text-4xl">
            What our alumni say
        </h2>
        <div class="mt-12 grid grid-cols-1 gap-6 md:grid-cols-3">
            @foreach([
                ['quote' => 'AlmaConnect made it dead simple to find my old batchmates. I\'m catching up with people I haven\'t spoken to in 12 years.', 'name' => 'Rahul Sharma', 'meta' => 'CSE 2012 · Senior Engineer, Google'],
                ['quote' => 'I posted a junior role at my startup and had 15 qualified alumni applications within 48 hours. The quality is unreal.', 'name' => 'Anjali Patel', 'meta' => 'ECE 2015 · Founder, NimbusAI'],
                ['quote' => 'The donation portal is elegant and transparent. I set up a recurring contribution to the scholarship fund in under 2 minutes.', 'name' => 'Nikhil Kumar', 'meta' => 'ME 2010 · Director of Engineering, Razorpay'],
            ] as $i => $t)
            @php $dirs = ['left', 'up', 'right']; @endphp
            <blockquote data-reveal="{{ $dirs[$i] }}"
                class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <p class="text-base leading-relaxed text-gray-700">"{{ $t['quote'] }}"</p>
                <footer class="mt-4">
                    <p class="font-semibold text-gray-900">{{ $t['name'] }}</p>
                    <p class="text-sm text-gray-500">{{ $t['meta'] }}</p>
                </footer>
            </blockquote>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════════════════ FAQ ══ --}}
<section id="faq" class="bg-gray-50 py-24">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <h2 data-reveal="up" class="text-center text-3xl font-bold tracking-tight text-gray-900 md:text-4xl">
            Frequently asked questions
        </h2>

        <div class="mt-10 divide-y divide-gray-200 rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            @foreach([
                ['q' => 'Who can join AlmaConnect?',         'a' => 'Any alumnus or current student of our institute. Alumni accounts require verification against the official roster; students with an @institute.edu email are approved instantly.'],
                ['q' => 'How is my data protected?',          'a' => 'Your data is stored on servers in India, encrypted at rest and in transit. We never sell or share your data with third parties. You can delete your account at any time.'],
                ['q' => 'How do donations work?',             'a' => 'Donations are processed securely via Razorpay (RBI-regulated). You get a receipt instantly. All campaigns are verified by the alumni cell before going live.'],
                ['q' => 'Can I post a job?',                  'a' => 'Yes — any approved alumnus can post a job or internship. Listings are visible only to verified network members, so you get serious applicants.'],
                ['q' => 'What if my profile info is wrong?',  'a' => 'Edit it any time from your profile page. Batch and branch can be corrected; if there\'s a verification mismatch, contact the alumni cell directly.'],
                ['q' => 'How do I delete my account?',        'a' => 'Go to Profile → Account Settings → Delete account. Your data is removed within 30 days. Donation records are anonymised rather than deleted to comply with financial regulations.'],
                ['q' => 'Is this an official institute platform?', 'a' => 'AlmaConnect is built and operated by the alumni cell. While not officially mandated by the institute administration, the data and access policies are agreed with the student welfare office.'],
                ['q' => 'Can I help moderate content?',       'a' => 'Reach out to the alumni cell if you\'d like to help. Group moderators can be appointed by the cell. We\'re always looking for engaged alumni.'],
            ] as $idx => $item)
            <div
                x-data="{ open: false }"
                class="px-5"
            >
                <button
                    class="flex w-full items-center justify-between py-4 text-left text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500"
                    @click="open = !open"
                    :aria-expanded="open"
                >
                    <span>{{ $item['q'] }}</span>
                    <svg
                        class="ml-4 h-5 w-5 shrink-0 text-gray-500 transition-transform duration-200"
                        :class="open ? 'rotate-180' : ''"
                        fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                    </svg>
                </button>
                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    class="pb-4 text-sm leading-relaxed text-gray-600"
                    style="display:none"
                >
                    {{ $item['a'] }}
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ════════════════════════════════════════════════════ FINAL CTA ══ --}}
<section data-reveal="up" class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-700 py-24 text-white">
    <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight md:text-4xl">
            Ready to reconnect?
        </h2>
        <p class="mx-auto mt-4 max-w-xl text-lg text-white/80">
            Join {{ number_format($stats['alumni']) }} alumni already on AlmaConnect. It takes less than 2 minutes.
        </p>
        <a href="{{ route('register') }}"
           class="mt-8 inline-block rounded-xl bg-white px-10 py-3.5 text-base font-semibold text-indigo-700 shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-white/50">
            Create your account
        </a>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════ FOOTER ══ --}}
<footer class="bg-gray-900 py-16 text-gray-400">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 gap-8 md:grid-cols-4">
            <div class="col-span-2 md:col-span-1">
                <p class="text-lg font-bold text-white">AlmaConnect 🎓</p>
                <p class="mt-2 text-sm leading-6">
                    The alumni network for our institute. Built by alumni, for alumni.
                </p>
                <p class="mt-4 text-xs text-gray-600">© {{ date('Y') }} AlmaConnect. All rights reserved.</p>
            </div>
            <div>
                <h3 class="text-xs font-semibold uppercase tracking-widest text-gray-300">Platform</h3>
                <ul class="mt-4 space-y-2 text-sm">
                    <li><a href="#features" class="hover:text-white transition">Features</a></li>
                    <li><a href="#stories" class="hover:text-white transition">Stories</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-white transition">Events</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-white transition">Donate</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-xs font-semibold uppercase tracking-widest text-gray-300">Account</h3>
                <ul class="mt-4 space-y-2 text-sm">
                    <li><a href="{{ route('login') }}" class="hover:text-white transition">Log in</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-white transition">Register</a></li>
                    <li><a href="{{ route('password.request') }}" class="hover:text-white transition">Forgot password</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-xs font-semibold uppercase tracking-widest text-gray-300">Contact</h3>
                <ul class="mt-4 space-y-2 text-sm">
                    <li>
                        <button
                            @click="feedbackOpen = true"
                            class="hover:text-white transition focus:outline-none focus:underline"
                        >
                            Send feedback
                        </button>
                    </li>
                    <li><a href="mailto:{{ config('almaconnect.contact_email') }}" class="hover:text-white transition">{{ config('almaconnect.contact_email') }}</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>

{{-- ══════════════════════════════════════════ FEEDBACK MODAL (Alpine) ══ --}}
@csrf
<div
    x-show="feedbackOpen"
    x-transition:enter="transition duration-250 ease-out"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
    @click.self="feedbackOpen = false"
    style="display: none"
    role="dialog"
    aria-label="Send feedback"
>
    <div
        x-data="{
            cat: 'suggestion',
            name: '',
            email: '',
            msg: '',
            err: {},
            ok: false,
            sending: false,
            async send() {
                if (this.sending) return;
                this.sending = true; this.err = {};
                try {
                    const r = await fetch('/feedback', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-XSRF-TOKEN': decodeURIComponent(document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? ''),
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ category: this.cat, name: this.name, email: this.email, message: this.msg }),
                    });
                    const d = await r.json();
                    if (r.ok) { this.ok = true; setTimeout(() => { feedbackOpen = false; this.ok = false; }, 2000); }
                    else { this.err = d.errors ?? {}; }
                } catch { this.err = { message: ['Could not send. Please try again.'] }; }
                finally { this.sending = false; }
            }
        }"
        class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl"
        @click.stop
    >
        <div class="flex items-start justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Send feedback</h2>
            <button type="button" @click="feedbackOpen = false" class="text-gray-400 hover:text-gray-700" aria-label="Close">&times;</button>
        </div>

        <div x-show="ok" class="mt-4 rounded-lg bg-green-50 p-3 text-sm text-green-700">
            Thanks! We've received your feedback.
        </div>

        <form x-show="!ok" class="mt-4 space-y-4" @submit.prevent="send()">
            <div>
                <p class="text-sm font-medium text-gray-700">Category</p>
                <div class="mt-2 flex gap-2">
                    <template x-for="c in ['bug','suggestion','general']">
                        <label :class="cat===c ? 'border-indigo-600 bg-indigo-50 text-indigo-700' : 'border-gray-300 text-gray-700'"
                               class="cursor-pointer rounded-lg border px-3 py-1.5 text-sm font-medium transition">
                            <input type="radio" :value="c" x-model="cat" class="sr-only">
                            <span x-text="c.charAt(0).toUpperCase()+c.slice(1)"></span>
                        </label>
                    </template>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input x-model="name" type="text" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <p x-show="err.name" class="mt-1 text-xs text-red-600" x-text="(err.name||[])[0]"></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input x-model="email" type="email" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <p x-show="err.email" class="mt-1 text-xs text-red-600" x-text="(err.email||[])[0]"></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Message</label>
                <textarea x-model="msg" rows="4" maxlength="5000" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                <div class="mt-0.5 flex justify-between text-xs text-gray-400">
                    <span x-show="err.message" class="text-red-600" x-text="(err.message||[])[0]"></span>
                    <span x-text="msg.length + ' / 5000'" class="ml-auto"></span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" :disabled="sending"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-60">
                    <span x-text="sending ? 'Sending…' : 'Send feedback'"></span>
                </button>
                <button type="button" @click="feedbackOpen = false" class="text-sm text-gray-500 hover:text-gray-900">Cancel</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>

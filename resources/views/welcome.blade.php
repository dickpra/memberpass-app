<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>{{ $settings->site_title ?? 'WFIEd Membership' }}</title>
    <meta name="description" content="{{ $settings->site_description ?? 'Join our exclusive community.' }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        /* Glass Effect */
        .glass { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(255,255,255,0.5); }
        .glass-card { background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.3); }

        /* Gradients */
        .text-gradient {
            background: linear-gradient(135deg, #16a34a 0%, #0d9488 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .bg-gradient-primary { background: linear-gradient(135deg, #16a34a 0%, #059669 100%); }
        
        /* Pattern Background */
        .hero-pattern {
            background-color: #f8fafc;
            background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
            background-size: 40px 40px;
        }
    </style>
</head>
<body class="antialiased text-slate-600 bg-slate-50">

    {{-- ================= NAVBAR ================= --}}
    <nav class="fixed w-full z-50 transition-all duration-300 glass">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                
                {{-- Logo --}}
                <div class="flex items-center gap-3">
                    @if(!empty($settings->site_logo))
                        <img src="{{ asset('storage/' . $settings->site_logo) }}" class="h-9 w-auto" alt="Logo">
                    @else
                        {{-- Fallback Logo Text --}}
                        <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center text-white font-bold text-xl">W</div>
                    @endif
                    <span class="font-bold text-xl tracking-tight text-slate-900">
                        {{ $settings->site_title ?? 'WFIEd' }}
                    </span>
                </div>

                {{-- Nav Links --}}
                <div class="hidden md:flex items-center gap-8">
                    <a href="#benefits" class="text-sm font-semibold text-slate-600 hover:text-green-600 transition">Benefits</a>
                    <a href="#pricing" class="text-sm font-semibold text-slate-600 hover:text-green-600 transition">Membership</a>
                    <a href="#faq" class="text-sm font-semibold text-slate-600 hover:text-green-600 transition">FAQ</a>
                </div>

                {{-- Auth Buttons --}}
                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ url('/member') }}" class="font-semibold text-slate-700 hover:text-green-600">Dashboard</a>
                    @else
                        <a href="{{ url('/member/login') }}" class="hidden md:block font-semibold text-slate-600 hover:text-slate-900">Sign In</a>
                        <a href="{{ url('/member/register') }}" class="bg-slate-900 hover:bg-black text-white px-5 py-2.5 rounded-full font-bold text-sm transition transform hover:scale-105 shadow-lg">
                            Join Membership
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- ================= HERO SECTION ================= --}}
    <section class="hero-pattern pt-40 pb-20 lg:pt-52 lg:pb-32 relative overflow-hidden">
        <div class="max-w-5xl mx-auto px-4 text-center relative z-10">
            
            {{-- NEW: Badge Update --}}
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white border border-slate-200 shadow-sm text-slate-600 text-xs font-bold uppercase tracking-wider mb-8 animate-fade-in-up">
                <span class="relative flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                </span>
                Open for New Registration {{ date('Y') }}
            </div>

            {{-- Main Heading --}}
            <h1 class="text-5xl md:text-7xl font-extrabold text-slate-900 tracking-tight mb-6 leading-[1.1]">
                Unlock Your <br class="hidden md:block" />
                <span class="text-gradient">Exclusive Privileges</span>
            </h1>

            <p class="mt-6 max-w-2xl mx-auto text-lg md:text-xl text-slate-500 leading-relaxed">
                {{ $settings->site_description ?? 'Akses ekosistem eksklusif untuk profesional, dapatkan sumber daya premium, dan bangun koneksi yang berarti.' }}
            </p>

            {{-- NEW: Social Proof (Avatar Stack) --}}
            <div class="mt-10 flex flex-col items-center justify-center gap-4 animate-fade-in-up">
                <div class="flex items-center -space-x-3">
                    {{-- Dummy Avatars --}}
                    <img class="w-10 h-10 rounded-full border-2 border-white" src="https://ui-avatars.com/api/?name=Alex+M&background=c7d2fe&color=3730a3" alt="">
                    <img class="w-10 h-10 rounded-full border-2 border-white" src="https://ui-avatars.com/api/?name=Sarah+J&background=fecaca&color=991b1b" alt="">
                    <img class="w-10 h-10 rounded-full border-2 border-white" src="https://ui-avatars.com/api/?name=Budi+S&background=bbf7d0&color=166534" alt="">
                    <img class="w-10 h-10 rounded-full border-2 border-white" src="https://ui-avatars.com/api/?name=Rina+A&background=fed7aa&color=9a3412" alt="">
                    <div class="w-10 h-10 rounded-full border-2 border-white bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600">
                        +{{ $totalMembers > 99 ? '99' : $totalMembers }}
                    </div>
                </div>
                <p class="text-sm font-medium text-slate-500">
                    Join with <span class="text-slate-900 font-bold">50+</span> countries around the world.
                </p>
            </div>

            {{-- CTA Buttons --}}
            <div class="mt-10 flex flex-col sm:flex-row justify-center gap-4">
                <a href="#pricing" class="px-8 py-4 bg-gradient-primary text-white rounded-xl font-bold text-lg shadow-xl shadow-green-500/20 hover:shadow-green-500/40 transition transform hover:-translate-y-1">
                    See Membership Plans
                </a>
            </div>
        </div>
    </section>

    {{-- ================= NEW: FEATURES / BENEFITS ================= --}}
    <section id="benefits" class="py-24 bg-white relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900">Why Join WFIEd?</h2>
                <p class="mt-4 text-slate-500 max-w-2xl mx-auto">We provide more than just membership cards. This is an investment for your professional future.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                {{-- Feature 1 --}}
                <div class="p-8 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-white hover:shadow-xl transition duration-300">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Wide Networking</h3>
                    <p class="text-slate-500 leading-relaxed">Connect with hundreds of professionals from various industries. Open up new business collaboration opportunities.</p>
                </div>

                {{-- Feature 2 --}}
                <div class="p-8 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-white hover:shadow-xl transition duration-300">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Premium Resources</h3>
                    <p class="text-slate-500 leading-relaxed">Access exclusive materials, business templates, and webinar recordings not available to the public.</p>
                </div>

                {{-- Feature 3 --}}
                <div class="p-8 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-white hover:shadow-xl transition duration-300">
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Certification & Status</h3>
                    <p class="text-slate-500 leading-relaxed">Get professional recognition with official membership status and digital member cards.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= PRICING SECTION ================= --}}
    <section id="pricing" class="py-24 bg-slate-50 relative border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="text-green-600 font-bold tracking-wider uppercase text-sm">Investment</span>
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mt-2">Choose Your Plan</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                
                @foreach($tiers as $tier)
                    @php
                        // 1. Tentukan Mata Uang & Harga (Ambil dari Setting)
                        $currency = $settings->site_currency ?? 'IDR';

                        if ($currency === 'USD') {
                            $price = $tier->price_usd;
                            $original = $tier->original_price_usd;
                            $symbol = '$';
                            $displayPrice = number_format($price, 2);
                            $displayOriginal = $original ? number_format($original, 2) : null;
                        } else {
                            $price = $tier->price_idr;
                            $original = $tier->original_price_idr;
                            $symbol = 'IDR'; // atau Rp
                            $displayPrice = number_format($price, 0, ',', '.');
                            $displayOriginal = $original ? number_format($original, 0, ',', '.') : null;
                        }
                    @endphp

                    <div class="relative bg-white/70 bg-gradient-to-br from-green-100/70 via-green-50/60 to-white/80 rounded-3xl p-8 shadow-lg border border-slate-200 flex flex-col hover:border-green-500 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 backdrop-blur-md">
                        
                        {{-- === BADGE DISKON (PUBLIC) === --}}
                        @if($original > $price)
                            @php
                                $saving = $original - $price;
                                $percent = round(($saving / $original) * 100);
                            @endphp
                            <div class="absolute top-0 right-0 bg-red-600 text-white text-xs font-bold px-4 py-2 rounded-tr-3xl rounded-bl-2xl shadow-md z-10 tracking-wider">
                                DISCOUNT {{ $percent }}%
                            </div>
                        @endif

                        <h3 class="text-xl font-bold text-slate-900 uppercase tracking-wide">{{ $tier->name }}</h3>
                        
                        <div class="mt-4 flex flex-col">
                            {{-- HARGA CORET --}}
                            @if($original > $price)
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-slate-400 line-through font-medium">
                                        {{ $symbol }} {{ $displayOriginal }}
                                    </span>
                                    <span class="text-[10px] font-bold text-red-500 bg-red-50 px-1.5 py-0.5 rounded">
                                        HEMAT {{ $symbol }} {{ $currency == 'USD' ? number_format($saving, 2) : number_format($saving, 0, ',', '.') }}
                                    </span>
                                </div>
                            @endif
                            
                            {{-- HARGA FINAL --}}
                            <div class="flex items-baseline text-slate-900 mt-1">
                                <span class="text-5xl font-extrabold tracking-tight text-green-600">
                                    @if($currency == 'USD')$@endif{{ $displayPrice }}
                                </span>
                                <span class="ml-2 text-sm font-semibold text-slate-500">
                                    @if($currency == 'IDR') IDR @endif /year
                                </span>
                            </div>
                            
                            <p class="text-xs text-slate-500 mt-2 italic">
                                * Full access membership for 1 year
                            </p>
                        </div>
                        
                        <hr class="my-6 border-slate-100">

                        {{-- BENEFITS LIST --}}
                        <ul class="space-y-4 mb-8 flex-grow">
                            @if($tier->benefits)
                                @foreach($tier->benefits as $benefit)
                                    <li class="flex items-start">
                                        <svg class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        <span class="ml-3 text-sm text-slate-600">{{ is_array($benefit) ? $benefit['text'] : $benefit }}</span>
                                    </li>
                                @endforeach
                            @endif
                        </ul>

                        <a href="{{ url('/member/register') }}" class="w-full block bg-slate-900 text-white text-center py-4 rounded-xl font-bold hover:bg-green-600 transition duration-300 shadow-xl">
                            Join Now for @if($currency == 'USD')$@endif{{ $displayPrice }}
                        </a>
                    </div>
                @endforeach
                
                {{-- VIP Card (Teaser) --}}
                <div class="relative bg-slate-900 rounded-3xl p-8 shadow-2xl flex flex-col text-white overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-full bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
                    <div class="absolute -right-10 -top-10 w-32 h-32 bg-yellow-500 blur-3xl opacity-20"></div>

                    <div class="relative z-10">
                        <div class="flex justify-between items-start">
                            <h3 class="text-xl font-bold text-yellow-400 uppercase tracking-wide">VIP LIFETIME</h3>
                            <span class="bg-yellow-500/20 text-yellow-300 border border-yellow-500/50 text-[10px] font-bold px-2 py-1 rounded">INVITE ONLY</span>
                        </div>
                        
                        <div class="mt-4 flex items-baseline">
                            <span class="text-3xl font-extrabold tracking-tight">EXCLUSIVE</span>
                        </div>
                        
                        <hr class="my-6 border-slate-700">

                        <ul class="space-y-4 mb-8 flex-grow">
                            <li class="flex items-start"><span class="text-yellow-500 mr-2">✦</span> Priority Support</li>
                            <li class="flex items-start"><span class="text-yellow-500 mr-2">✦</span> Lifetime Access</li>
                            <li class="flex items-start"><span class="text-yellow-500 mr-2">✦</span> Private Events</li>
                        </ul>

                        <button disabled class="w-full bg-slate-800 text-slate-400 py-3.5 rounded-xl font-bold cursor-not-allowed border border-slate-700">
                            Contact Admin
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ================= NEW: FAQ SECTION ================= --}}
    <section id="faq" class="py-20 bg-white">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center text-slate-900 mb-10">Frequently Asked Questions</h2>
            
            <div class="space-y-4">
                {{-- FAQ 1 --}}
                <div class="border border-slate-200 rounded-lg p-5 hover:border-green-400 transition cursor-pointer group">
                    <h3 class="font-bold text-slate-800 flex justify-between items-center">
                        How to join membership?
                        <span class="text-slate-400 group-hover:text-green-500">↓</span>
                    </h3>
                    <p class="text-sm text-slate-500 mt-2 hidden group-hover:block transition-all">
                        Click the "Join Membership" button above, fill out the registration form, and make the payment as instructed. Your account will be activated after admin verification.
                    </p>
                </div>

                {{-- FAQ 2 --}}
                <div class="border border-slate-200 rounded-lg p-5 hover:border-green-400 transition cursor-pointer group">
                    <h3 class="font-bold text-slate-800 flex justify-between items-center">
                        What payment methods are available?
                        <span class="text-slate-400 group-hover:text-green-500">↓</span>
                    </h3>
                    <p class="text-sm text-slate-500 mt-2 hidden group-hover:block transition-all">
                        We accept bank transfers to the official company accounts listed during the checkout process.
                    </p>
                </div>

                {{-- FAQ 3 --}}
                <div class="border border-slate-200 rounded-lg p-5 hover:border-green-400 transition cursor-pointer group">
                    <h3 class="font-bold text-slate-800 flex justify-between items-center">
                        Can membership be canceled?
                        <span class="text-slate-400 group-hover:text-green-500">↓</span>
                    </h3>
                    <p class="text-sm text-slate-500 mt-2 hidden group-hover:block transition-all">
                        Membership is valid for 1 year. Cancellation during the period does not receive a refund.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= UPDATED FOOTER ================= --}}
    <footer class="bg-slate-900 text-slate-300 py-12 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                
                {{-- Brand Info --}}
                <div class="md:col-span-2">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="text-white font-bold text-xl">{{ $settings->site_title ?? 'WFIEd' }}</span>
                    </div>
                    <p class="text-slate-400 text-sm leading-relaxed max-w-sm">
                        {{ $settings->footer_text ?? 'Building the future of professional networking. Join us and grow together.' }}
                    </p>
                </div>

                {{-- Links --}}
                <div>
                    <h4 class="text-white font-bold mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#benefits" class="hover:text-green-400 transition">Benefits</a></li>
                        <li><a href="#pricing" class="hover:text-green-400 transition">Pricing</a></li>
                        <li><a href="{{ url('/member/login') }}" class="hover:text-green-400 transition">Member Login</a></li>
                    </ul>
                </div>

                {{-- Contact Info (UPDATED) --}}
                <div>
                    <h4 class="text-white font-bold mb-4">Contact Us</h4>
                    <ul class="space-y-3 text-sm">
                        @if(!empty($settings->support_email))
                            <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                <a href="mailto:{{ $settings->support_email }}" class="hover:text-white transition">{{ $settings->support_email }}</a>
                            </li>
                        @endif

                        @if(!empty($settings->support_phone))
                            <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                <a href="https://wa.me/62{{ $settings->support_phone }}" target="_blank" class="hover:text-white transition">+62 {{ $settings->support_phone }}</a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            <div class="border-t border-slate-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} {{ $settings?->site_title ?? 'WFIEd Membership' }}. All rights reserved.</p>
                <div class="flex gap-4">
                    <a href="#" class="hover:text-white">Privacy Policy</a>
                    <a href="#" class="hover:text-white">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
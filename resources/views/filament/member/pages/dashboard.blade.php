<x-filament-panels::page>
    {{-- 1. CSS CUSTOM --}}
    <style>
        /* --- LAYOUT --- */
        .membership-container { max-width: 1000px; margin: 0 auto; padding: 2rem 1rem; }

        /* --- CARD STRUCTURE --- */
        .pricing-card {
            position: relative; background: white; border-radius: 1.5rem; overflow: hidden;
            display: flex; flex-direction: column; box-shadow: 0 10px 40px -10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease; border: 1px solid rgba(0,0,0,0.05);
        }
        .pricing-card:hover { transform: translateY(-5px); }
        .card-header { padding: 2.5rem; color: white; text-align: center; position: relative; }
        .card-body { padding: 2rem; background: white; flex-grow: 1; display: flex; flex-direction: column; }

        /* --- TIPE 1: GREENCARD (Selection) --- */
        .pricing-card.green { border-top: 5px solid #22c55e; margin-top: 1rem; }
        .pricing-card.green .card-header {
            background: linear-gradient(135deg, #16a34a 0%, #14532d 100%);
            clip-path: ellipse(150% 100% at 50% 0%);
        }
        .pricing-card.green h3 { font-size: 2rem; font-weight: 800; letter-spacing: -1px; }
        .pricing-card.green .price-tag { font-size: 3rem; font-weight: 900; }
        .pricing-card.green .sub-text { opacity: 0.9; font-size: 0.9rem; margin-top: 0.5rem; }
        .pricing-card.green .btn-action {
            background: #16a34a; color: white; padding: 1rem; border-radius: 0.75rem;
            font-weight: bold; width: 100%; margin-top: auto; border: none; cursor: pointer; transition: 0.3s;
        }
        .pricing-card.green .btn-action:hover { background: #14532d; }

        /* --- TIPE 2: VIP LIFETIME (Active View) --- */
        .pricing-card.vip-lifetime {
            background: #0f172a; border: 1px solid #334155; color: white; margin-top: 1rem;
        }
        .pricing-card.vip-lifetime .card-header {
            background: linear-gradient(45deg, #000000, #1e293b); border-bottom: 1px solid #334155; padding-bottom: 3rem;
        }
        .pricing-card.vip-lifetime h3 {
            background: linear-gradient(to right, #fbbf24, #d97706);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            font-size: 2.5rem; font-weight: 900; text-transform: uppercase;
        }
        .pricing-card.vip-lifetime .badge-infinite {
            position: absolute; top: 1rem; right: 1rem;
            background: linear-gradient(90deg, #d97706, #fbbf24); color: black;
            font-weight: bold; font-size: 0.7rem; padding: 0.25rem 0.75rem; border-radius: 99px;
        }
        .pricing-card.vip-lifetime .card-body { background: #0f172a; color: #cbd5e1; }

        /* --- OVERRIDES: VIP + GREEN (Active Green View) --- */
        .pricing-card.vip-lifetime.green {
            background: linear-gradient(135deg, #0f4d2f 0%, #0b3924 40%, #123d2a 100%);
            border: 1px solid rgba(6, 95, 70, 0.6); color: #e6f7ea;
        }
        .pricing-card.vip-lifetime.green .card-header {
            background: linear-gradient(135deg, #16a34a 0%, #0f5130 100%);
            clip-path: ellipse(150% 100% at 50% 0%);
            border-bottom: 1px solid rgba(255,255,255,0.03); padding-bottom: 2.5rem;
        }
        .pricing-card.vip-lifetime.green h3 {
            font-size: 2.2rem; font-weight: 900; letter-spacing: -0.5px;
            color: #f0fff4; -webkit-text-fill-color: unset; text-transform: uppercase;
        }
        .pricing-card.vip-lifetime.green .card-body {
            background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.00)); color: #e6f7ea;
        }
        .pricing-card.vip-lifetime.green .features-list li { color: #dff7e7; }
        .pricing-card.vip-lifetime.green .features-list svg { color: #bbf7d0; }
        
        /* Glossy effect */
        .pricing-card.vip-lifetime.green::after {
            content: ''; position: absolute; left: -20%; top: -40%; width: 140%; height: 60%;
            transform: rotate(-20deg); pointer-events: none; mix-blend-mode: overlay; opacity: 0.55;
            background: linear-gradient(90deg, rgba(255,255,255,0.18), rgba(255,255,255,0.02) 40%, rgba(255,255,255,0));
        }

        /* --- TIPE 3: INACTIVE / EXPIRED (DEAD CARD) --- */
        .pricing-card.inactive {
            border: 1px solid #cbd5e1; background: #f8fafc; opacity: 0.95;
        }
        .pricing-card.inactive .card-header {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
            clip-path: ellipse(150% 100% at 50% 0%); color: #e2e8f0;
        }
        .pricing-card.inactive h3 {
            color: #f1f5f9; text-shadow: none; -webkit-text-fill-color: unset; 
        }
        .pricing-card.inactive .card-body { background: #f8fafc; color: #64748b; }

        /* FIX UKURAN ICON SAAT INACTIVE */
        .pricing-card.inactive .features-list li { color: #94a3b8; }
        .pricing-card.inactive .features-list svg {
            color: #cbd5e1; width: 1.25rem; height: 1.25rem; min-width: 1.25rem; margin-right: 0.5rem;
        }

        /* TOMBOL REACTIVATE */
        .btn-reactivate {
            width: 100%; background-color: #dc2626; color: white; font-weight: bold;
            padding: 0.75rem; border-radius: 0.5rem; margin-top: 1rem; transition: 0.2s;
            border: 1px solid #b91c1c; display: flex; justify-content: center; align-items: center; gap: 0.5rem;
        }
        .btn-reactivate:hover { background-color: #b91c1c; transform: scale(1.02); }

        /* COMMON HELPERS */
        .features-list { list-style: none; padding: 0; margin-bottom: 2rem; }
        .features-list li { display: flex; align-items: center; margin-bottom: 0.75rem; font-size: 1rem; color: #334155; }
        .pricing-card.vip-lifetime .features-list li { color: #cbd5e1; }
        .pricing-card.green .features-list svg { color: #16a34a; width: 1.25rem; margin-right: 0.5rem; }
        .pricing-card.vip-lifetime .features-list svg { color: #fbbf24; width: 1.25rem; margin-right: 0.5rem; }

        @media (max-width:640px){
            .card-header { padding: 1.6rem; }
            .card-body { padding: 1.2rem; }
            .pricing-card.vip-lifetime.green h3 { font-size: 1.6rem; }
        }
    </style>

    {{-- 2. PHP LOGIC (Renewal & Price) --}}
    @php
        $user = auth()->user();
        $settings = \App\Models\GeneralSetting::first();
        $now = \Carbon\Carbon::now();

        // A. Logic Prorated (Untuk Registered View)
        $greenTier = \App\Models\MembershipTier::where('name', 'GreenCard')->first();
        $displayPrice = 0; $priceNote = '';
        if ($greenTier) {
            $basePrice = $greenTier->price;
            if ($now->month == 12) {
                $displayPrice = $basePrice;
                $priceNote = "Early Bird " . ($now->year + 1);
            } else {
                $daysInYear = $now->copy()->endOfYear()->dayOfYear;
                $remaining = $now->diffInDays($now->copy()->endOfYear()) + 1;
                $calc = ($remaining / $daysInYear) * $basePrice;
                $min = $basePrice / 12;
                $displayPrice = ceil(max($calc, $min) / 1000) * 1000;
                $priceNote = "Prorated (Sisa Tahun Ini)";
            }
        }

        // B. Logic Renewal Banner
        $showRenewal = false; 
        $expiry = null; 
        if ($user && $user->expiry_date) {
            $expiry = \Carbon\Carbon::parse($user->expiry_date);
            $monthsLeft = $now->diffInMonths($expiry, false); 
            // Muncul jika Active/Inactive, sisa <3 bulan, dan bukan VIP
            $showRenewal = ($user->status === 'active' || $user->status === 'inactive') 
                            && $monthsLeft <= 3 
                            && $user->membership_type !== 'VIP Lifetime';
        }
    @endphp

    {{-- 3. BANNER RENEWAL (Merah) --}}
    @if($showRenewal && $expiry)
        <div class="max-w-4xl mx-auto mt-6 mb-2">
            <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-r shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <x-heroicon-s-exclamation-triangle class="w-6 h-6 text-red-600"/>
                        <h3 class="font-bold text-red-800 text-lg">Membership Renewal Required</h3>
                    </div>
                    <p class="text-red-700 mt-1">
                        @if(\Carbon\Carbon::now()->greaterThan($expiry))
                            Masa keanggotaan Anda <strong>SUDAH BERAKHIR</strong> pada {{ $expiry->format('d M Y') }}.
                        @else
                            Masa keanggotaan Anda akan berakhir pada <strong>{{ $expiry->format('d M Y') }}</strong>.
                        @endif
                        Lakukan perpanjangan sekarang untuk tahun berikutnya.
                    </p>
                </div>
                <div>
                    <button type="button" wire:click="mountAction('renewMembership')" 
                        class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg shadow transition transform hover:scale-105"
                        style="color: #fff !important; background-color: #dc2626 !important;">
                        Perpanjang Sekarang
                    </button>
                    {{-- Render Action Wrapper --}}
                    {{-- {{ ($this->renewMembershipAction)() }} --}}
                </div>
            </div>
        </div>
    @endif

    <div class="membership-container">

        {{-- ================================================= --}}
        {{-- ALUR 1: REGISTERED (BELUM PILIH PAKET)            --}}
        {{-- ================================================= --}}
        @if($user->status === 'registered')
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800">Choose Your Membership</h2>
                <p class="text-gray-500 mt-2">Bergabunglah dengan komunitas eksklusif kami.</p>
            </div>
            <div class="max-w-md mx-auto">
                @php
                    $publicTier = \App\Models\MembershipTier::where('is_active', true)
                                    ->where('is_invitation_only', false)->first();
                @endphp
                @if($publicTier)
                    <div class="pricing-card green">
                        <div class="card-header">
                            <h3 class="uppercase">{{ $publicTier->name }}</h3>
                            <div class="mt-4">
                                <span class="text-lg align-top opacity-75">IDR</span>
                                <span class="price-tag">{{ number_format($displayPrice / 1000) }}K</span>
                            </div>
                            <div class="sub-text">{{ $priceNote }}</div>
                            <div class="mt-2 text-xs opacity-75 bg-white/20 inline-block px-3 py-1 rounded-full">
                                Valid until 31 Dec {{ $now->month == 12 ? $now->year + 1 : $now->year }}
                            </div>
                        </div>
                        <div class="card-body">
                            <ul class="features-list">
                                @if($publicTier->benefits)
                                    @foreach($publicTier->benefits as $b)
                                        <li><x-heroicon-s-check-circle /> {{ is_array($b) ? $b['text'] : $b }}</li>
                                    @endforeach
                                @endif
                            </ul>
                            <button type="button" 
                                wire:click="mountAction('selectTier', {{ json_encode(['id' => $publicTier->id, 'name' => $publicTier->name]) }})"
                                class="btn-action">
                                Join Membership Now
                            </button>
                        </div>
                    </div>
                @else
                    <div class="p-4 bg-gray-100 text-center rounded text-gray-500">Pendaftaran Membership sedang ditutup.</div>
                @endif
            </div>

        {{-- ================================================= --}}
        {{-- ALUR 2: WAITING PAYMENT (FORM & REKENING)         --}}
        {{-- ================================================= --}}
        @elseif($user->status === 'waiting_payment')
             <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                {{-- KIRI: ORDER SUMMARY & REKENING --}}
                <div class="md:col-span-1 space-y-6">
                    <div class="p-6 bg-white rounded-xl shadow-sm border border-gray-200">
                        <h2 class="text-lg font-bold mb-4 text-gray-800 border-b pb-2">Order Summary</h2>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-500 text-sm">Package</span>
                            <span class="font-bold text-primary-600 text-sm">{{ $user->membership_type }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-gray-500 text-sm">Total</span>
                            @php 
                                $payment = \App\Models\Payment::where('user_id', $user->id)->where('status', 'pending_upload')->latest()->first(); 
                                $amount = $payment ? $payment->amount : 0;
                            @endphp
                            <span class="font-bold text-xl">{{ $settings->currency ?? 'IDR' }} {{ number_format($amount) }}</span>
                        </div>
                        <div class="flex flex-col gap-3 pt-4 border-t">
                             {{ $this->downloadInvoiceAction }}
                             {{ $this->cancelOrderAction }}
                        </div>
                    </div>
                    
                    <div class="p-6 rounded-xl border bg-blue-50 border-blue-100 text-sm">
                         <h2 class="text-base font-bold mb-3 text-blue-900 border-b border-blue-200 pb-2">Transfer Destination</h2>
                         <div class="mb-4">
                             <p class="text-xs text-blue-500 uppercase tracking-wider font-semibold">Bank Name</p>
                             <p class="font-bold text-lg text-gray-800">{{ $settings->bank_name ?? 'BCA' }}</p>
                         </div>
                         <div class="mb-4">
                             <p class="text-xs text-blue-500 uppercase tracking-wider font-semibold">Account Number</p>
                             <p class="font-mono text-xl font-bold text-gray-900 bg-white px-2 py-1 rounded border border-blue-100 inline-block">
                                {{ $settings->bank_account_number ?? '123456' }}
                             </p>
                         </div>
                         <div class="mb-4">
                             <p class="text-xs text-blue-500 uppercase tracking-wider font-semibold">Beneficiary</p>
                             <p class="font-bold text-gray-800">{{ $settings->bank_account_owner ?? 'PT WFIED' }}</p>
                         </div>
                    </div>
                </div>

                {{-- KANAN: FORM UPLOAD --}}
                <div class="md:col-span-2 p-6 bg-white rounded-xl shadow-sm border border-gray-200">
                    <h2 class="text-lg font-bold mb-1 text-gray-800">Confirm Payment</h2>
                    <p class="text-sm text-gray-500 mb-6">Silakan upload bukti transfer agar keanggotaan Anda segera aktif.</p>
                    
                    <x-filament-panels::form wire:submit="submitPayment">
                        {{ $this->form }}
                        <div class="mt-6 flex justify-end pt-4 border-t">
                            <x-filament::button type="submit" size="lg">Submit Payment Proof</x-filament::button>
                        </div>
                    </x-filament-panels::form>
                </div>
            </div>

        {{-- ================================================= --}}
        {{-- ALUR 3: MEMBER ACTIVE / INACTIVE / EXPIRED        --}}
        {{-- ================================================= --}}
        @elseif($user->status === 'active' || $user->status === 'inactive') 

            @php
                // Logic cek Expired / Inactive
                $isInactive = $user->status === 'inactive' || \Carbon\Carbon::now()->greaterThan(\Carbon\Carbon::parse($user->expiry_date));
                
                // Tentukan Class CSS berdasarkan Status
                if ($isInactive) {
                    $cardClass = 'inactive';
                } else {
                    $cardClass = ($user->membership_type === 'VIP Lifetime') ? 'vip-lifetime' : 'vip-lifetime green';
                }
            @endphp

            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-800">Welcome Back, {{ $user->name }}!</h2>
                @if($isInactive)
                    <p class="text-red-600 font-bold bg-red-50 inline-block px-3 py-1 rounded-full text-sm mt-2 border border-red-100">
                        Membership Expired / Inactive
                    </p>
                @else
                    <p class="text-gray-500">Your membership is active.</p>
                @endif
            </div>

            <div class="max-w-md mx-auto">
                
                <div class="pricing-card {{ $cardClass }}">
                    {{-- Badge Lifetime --}}
                    @if(!$isInactive && $user->membership_type === 'VIP Lifetime') 
                        <div class="badge-infinite">∞ LIFETIME PRIVILEGE</div> 
                    @endif
                    
                    <div class="card-header">
                        <h3>{{ $user->membership_type === 'VIP Lifetime' ? 'VIP ACCESS' : 'GREENCARD' }}</h3>
                        <p class="mt-2 tracking-widest font-bold opacity-80">MEMBER #{{ $user->member_id }}</p>
                    </div>

                    <div class="card-body">
                        <ul class="features-list">
                            @if($user->membership_type === 'VIP Lifetime')
                                <li><x-heroicon-s-star /> Full Access System</li>
                                <li><x-heroicon-s-star /> Priority VVIP Support</li>
                                <li><x-heroicon-s-star /> Exclusive Merchandise</li>
                            @else
                                <li><x-heroicon-s-check /> General System Access</li>
                                <li><x-heroicon-s-check /> Standard Support</li>
                                <li class="opacity-50"><x-heroicon-s-x-mark /> VIP Privileges</li>
                            @endif
                        </ul>

                        {{-- ACTION AREA --}}
                        @if($isInactive)
                            <div class="mt-auto">
                                <div class="text-center mb-2">
                                    <p class="text-xs text-red-500 font-bold uppercase">Membership Suspended</p>
                                </div>
                                <button type="button" wire:click="mountAction('renewMembership')" class="btn-reactivate">
                                    <x-heroicon-s-arrow-path class="w-5 h-5"/>
                                    REACTIVATE MEMBER
                                </button>
                            </div>
                        @else
                            <div class="text-center mt-4 p-3 bg-white/5 rounded border border-white/10">
                                <p class="text-xs opacity-70">Membership Status</p>
                                <p class="font-mono font-bold tracking-wide {{ $user->membership_type === 'VIP Lifetime' ? 'text-green-400' : 'text-green-300' }}">
                                    ACTIVE • UNTIL {{ \Carbon\Carbon::parse($user->expiry_date)->format('d M Y') }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        {{-- ================================================= --}}
        {{-- ALUR 4: STATUS LAIN (WAITING VERIF / REJECTED)    --}}
        {{-- ================================================= --}}
        @else
            <div class="p-8 text-center bg-gray-50 rounded-xl border">
                <h2 class="font-bold text-gray-700">Status: {{ ucfirst(str_replace('_', ' ', $user->status)) }}</h2>
                <p class="text-gray-500 text-sm mt-2">Mohon menunggu admin memproses data Anda.</p>
            </div>
        @endif

    </div>
</x-filament-panels::page>
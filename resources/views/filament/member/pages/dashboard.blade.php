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

        .strike-text {
            text-decoration: line-through;
            text-decoration-color: rgba(255, 255, 255, 0.8); /* Warna garis lebih tebal/jelas */
            text-decoration-thickness: 2px; /* Garis lebih tebal */
        }
        @media (max-width:640px){
            .card-header { padding: 1.6rem; }
            .card-body { padding: 1.2rem; }
            .pricing-card.vip-lifetime.green h3 { font-size: 1.6rem; }
        }
    </style>

    {{-- 2. PHP LOGIC (Renewal & Price) --}}
    @php
        $user = auth()->user();
        $now = \Carbon\Carbon::now();
        
        // Data ini dikirim dari Controller (getViewData), tapi kita set default agar tidak error
        $currency = $currency ?? 'IDR'; 
        
        // Logic Banner Renewal
        $showRenewal = false; 
        $expiry = null; 
        if ($user && $user->expiry_date) {
            $expiry = \Carbon\Carbon::parse($user->expiry_date);
            $monthsLeft = $now->diffInMonths($expiry, false); 
            $showRenewal = ($user->status === 'active' || $user->status === 'inactive') 
                            && $monthsLeft <= 3 
                            && $user->membership_type !== 'VIP Lifetime';
        }
    @endphp
    {{-- Ambil Bank dari Tabel Baru --}}
    @php
        $banks = \App\Models\BankAccount::where('is_active', true)->get();
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
                <p class="text-gray-500 mt-2">Pilih paket keanggotaan Anda ({{ $currency }}).</p>
            </div>
            <div class="max-w-md mx-auto">
                {{-- LOOPING TIERS DARI CONTROLLER --}}
                @foreach($tiers as $tier)
        @php
            // 1. TENTUKAN HARGA DASAR (USD/IDR)
            if ($currency === 'USD') {
                $basePrice = $tier->price_usd;
                $baseOriginal = $tier->original_price_usd;
                $symbol = '$';
            } else {
                $basePrice = $tier->price_idr;
                $baseOriginal = $tier->original_price_idr;
                $symbol = 'IDR';
            }

            // 2. LOGIC PRORATED (HITUNGAN)
            $now = \Carbon\Carbon::now();
            $finalPrice = 0;
            $infoText = "";
            $isProrated = false;

            if ($now->month == 12) {
                $finalPrice = $basePrice;
                $infoText = "Early Bird " . ($now->year + 1);
            } else {
                $daysInYear = $now->copy()->endOfYear()->dayOfYear;
                $remaining = $now->diffInDays($now->copy()->endOfYear()) + 1;
                
                $calc = ($remaining / $daysInYear) * $basePrice;
                $min = $basePrice / 12;
                
                if ($calc < $min) {
                    $finalPrice = $min;
                    $infoText = "Prorated (Min 1 Month)";
                } else {
                    $finalPrice = $calc;
                    $infoText = "Prorated: Sisa {$remaining} hari";
                }
                $isProrated = true;
            }

            // 3. PEMBULATAN
            if ($currency === 'IDR') {
                $finalPrice = ceil($finalPrice / 1000) * 1000;
            } else {
                $finalPrice = round($finalPrice, 2);
            }

            // 4. FORMAT TAMPILAN
            if ($currency === 'USD') {
                $displayPrice = number_format($finalPrice, 2);
                $displayOriginal = $baseOriginal ? number_format($baseOriginal, 2) : null;
                $displayBase = number_format($basePrice, 2);
            } else {
                $displayPrice = number_format($finalPrice, 0, ',', '.');
                $displayOriginal = $baseOriginal ? number_format($baseOriginal, 0, ',', '.') : null;
                $displayBase = number_format($basePrice, 0, ',', '.');
            }
        @endphp

        <div class="pricing-card green">
            {{-- === BADGE DISKON (MEMBER DASHBOARD) === --}}
            @if($baseOriginal > $basePrice)
                @php 
                    $saving = $baseOriginal - $basePrice;
                    $percent = round(($saving / $baseOriginal) * 100); 
                @endphp
                {{-- Badge Merah Mencolok --}}
                <div class="absolute top-0 right-0 bg-red-600 text-white text-xs font-bold px-3 py-1.5 rounded-bl-xl shadow-sm z-20 border-l border-b border-red-800" style="background: linear-gradient(90deg, #dc2626 60%, #b91c1c 100%);">
                    DISCOUNT {{ $percent }}%
                </div>
            @endif

            <div class="card-header">
                <h3 class="uppercase">{{ $tier->name }}</h3>
                
                {{-- AREA HARGA --}}
                <div class="mt-2 min-h-[90px] flex flex-col justify-center relative">
                    
                    {{-- 1. Harga Coret (Normal Tahunan) --}}
                    @if($baseOriginal > $basePrice)
                        {{-- Gunakan style manual agar tidak hilang oleh Tailwind Purge --}}
                        <div class="text-sm text-white/70 font-medium mb-0.5 strike-text" 
                             style="text-decoration: line-through; text-decoration-color: rgba(255,255,255,0.7);">
                            Normal: {{ $symbol }} {{ $displayOriginal }}
                        </div>
                    @endif

                    {{-- 2. Harga Final (Yang harus dibayar) --}}
                    <div>
                        <span class="text-lg align-top opacity-75 font-bold">{{ $symbol }}</span>
                        <span class="price-tag leading-none">{{ $displayPrice }}</span>
                    </div>

                    {{-- 3. Keterangan Prorated --}}
                    <div class="mt-2 inline-block bg-white/20 backdrop-blur-sm rounded px-2 py-1 text-xs font-medium text-white/90 border border-white/10">
                        {{ $infoText }}
                    </div>

                    {{-- 4. Info Harga Paket Asli (Jika sedang prorated) --}}
                    @if($isProrated)
                        <div class="text-[10px] mt-1 opacity-70 italic">
                            (Base Plan: {{ $symbol }} {{ $displayBase }} / year)
                        </div>
                    @endif
                </div>
            </div>

            <div class="card-body">
                <ul class="features-list">
                    @if($tier->benefits)
                        @foreach($tier->benefits as $b)
                            <li><x-heroicon-s-check-circle /> {{ is_array($b) ? $b['text'] : $b }}</li>
                        @endforeach
                    @endif
                </ul>
                <button type="button" 
                    wire:click="mountAction('selectTier', {{ json_encode(['id' => $tier->id, 'name' => $tier->name]) }})"
                    class="btn-action">
                    Select Plan
                </button>
            </div>
        </div>
    @endforeach
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
                                $payment = \App\Models\Payment::where('user_id', $user->id)
                                            ->where('status', 'pending_upload')
                                            ->latest()
                                            ->first(); 
                                $amount = $payment ? $payment->amount : 0;
                                $payCurrency = $payment ? $payment->currency : 'IDR';
                            @endphp
                            {{-- Tampilkan sesuai Currency Payment --}}
                            <span class="font-bold text-xl">
                                {{ $payCurrency }} 
                                {{ $payCurrency == 'USD' ? number_format($amount, 2) : number_format($amount, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex flex-col gap-3 pt-4 border-t">
                             {{ $this->downloadInvoiceAction }}
                             {{ $this->cancelOrderAction }}
                        </div>
                    </div>
                    
                    {{-- BOX REKENING & LEGALITAS (UPDATED) --}}
                    <div class="p-6 rounded-xl border bg-blue-50 border-blue-100 text-sm shadow-sm">
                         <h2 class="text-base font-bold mb-4 text-blue-900 border-b border-blue-200 pb-2">
                             Transfer Destination
                         </h2>

                         {{-- 1. INFO PENERIMA (ORGANIZATION / LEGAL) --}}
                         <div class="mb-4 bg-white/60 p-3 rounded border border-blue-100">
                             <p class="text-[10px] text-blue-500 font-bold uppercase tracking-wider mb-1">Organization Name</p>
                             
                             {{-- Nama PT / Organisasi --}}
                             <p class="font-bold text-gray-900 text-sm">
                                 {{ $settings->organization_name ?? 'WFIED Management' }}
                             </p>
                             
                             {{-- Alamat Fisik (Jika ada) --}}
                             @if(!empty($settings->organization_address))
                                 <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                                     {{ $settings->organization_address }}
                                 </p>
                             @endif

                             {{-- NPWP / TAX (Jika ada) --}}
                             @if(!empty($settings->tax_number))
                                 <div class="mt-2 inline-block bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded border border-blue-200">
                                     TAX/NPWP: {{ $settings->tax_number }}
                                 </div>
                             @endif
                         </div>

                         {{-- 2. LIST REKENING BANK --}}
                         <h3 class="text-[10px] text-blue-500 font-bold uppercase tracking-wider mb-2 mt-4">
                             Available Accounts
                         </h3>

                         @php
                             // Ambil Bank yang Aktif
                             $banks = \App\Models\BankAccount::where('is_active', true)->get();
                         @endphp

                         @if($banks->count() > 0)
                             <div class="space-y-3">
                                 @foreach($banks as $bank)
                                     <div class="bg-white p-3 rounded-lg border border-blue-200 shadow-sm relative group hover:border-blue-400 transition">
                                         
                                         {{-- Header: Logo & Nama Bank --}}
                                         <div class="flex items-center gap-3 mb-2 border-b border-gray-100 pb-2">
                                             {{-- @if($bank->logo)
                                                 <img src="{{ asset('storage/'.$bank->logo) }}" class="h-6 w-auto object-contain" alt="{{ $bank->bank_name }}">
                                             @else
                                                 <div class="h-6 w-6 bg-gray-100 rounded flex items-center justify-center text-[8px] font-bold text-gray-500">BANK</div>
                                             @endif --}}
                                             
                                             <div>
                                                 <p class="font-bold text-gray-800 text-xs uppercase">{{ $bank->bank_name }}</p>
                                                 @if($bank->swift_code)
                                                     <p class="text-[9px] text-gray-400 font-mono">SWIFT: {{ $bank->swift_code }}</p>
                                                 @endif
                                             </div>
                                         </div>

                                         {{-- Body: No Rekening & Kota --}}
                                         <div class="space-y-1">
                                             <div class="flex justify-between items-center">
                                                 <span class="text-gray-500 text-xs">Account No.</span>
                                                 <div class="flex items-center gap-2">
                                                     <span class="font-mono font-bold text-base text-blue-700">
                                                         {{ $bank->account_number }}
                                                     </span>
                                                     {{-- Tombol Copy --}}
                                                     <button type="button" 
                                                        onclick="navigator.clipboard.writeText('{{ $bank->account_number }}'); alert('Copied!')" 
                                                        class="text-gray-300 hover:text-blue-600 transition" title="Copy Number">
                                                         <x-heroicon-m-clipboard class="w-4 h-4"/>
                                                     </button>
                                                 </div>
                                             </div>

                                             <div class="flex justify-between items-start">
                                                 <span class="text-gray-500 text-xs">Holder</span>
                                                 <span class="text-xs font-medium text-gray-800 text-right">{{ $bank->account_owner }}</span>
                                             </div>

                                             @if($bank->bank_city)
                                                 <div class="flex justify-between items-center pt-1 mt-1 border-t border-gray-50">
                                                     <span class="text-gray-400 text-[10px]">Branch/City</span>
                                                     <span class="text-gray-500 text-[10px]">{{ $bank->bank_city }}</span>
                                                 </div>
                                             @endif
                                         </div>
                                     </div>
                                 @endforeach
                             </div>
                         @else
                             <div class="text-center py-4 bg-white rounded border border-dashed border-gray-300">
                                 <p class="text-xs text-gray-500 italic">No bank accounts available.</p>
                             </div>
                         @endif
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
        {{-- BLOK: PEMBAYARAN DITOLAK (REJECTED) --}}
@elseif($user->status === 'payment_rejected')

    <div class="p-6 rounded-xl bg-red-50 border border-red-200 shadow-sm animate-pulse">
        <div class="flex items-start gap-4">
            <div class="p-3 bg-red-100 rounded-full text-red-600">
                <x-heroicon-o-x-circle class="w-8 h-8" />
            </div>
            
            <div class="flex-1">
                <h2 class="text-xl font-bold text-red-700 mb-1">Pembayaran Ditolak</h2>
                <p class="text-red-600 mb-4">
                    Maaf, pembayaran Anda belum dapat kami setujui.
                </p>

                {{-- Tampilkan Alasan Admin --}}
                @if($latestPayment->admin_note)
                    <div class="bg-white/50 p-3 rounded-lg border border-red-100 mb-4">
                        <span class="text-xs font-bold text-red-500 uppercase">Alasan Penolakan:</span>
                        <p class="text-red-800 font-medium mt-1">
                            "{{ $latestPayment->admin_note }}"
                        </p>
                    </div>
                @endif

                <div class="flex gap-3 mt-4">
                    {{-- Tombol Try Again --}}
                    {{ $this->tryAgainAction }}
                    
                    {{-- Tombol Batalkan (Opsional) --}}
                    {{ $this->cancelOrderAction }}
                </div>
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
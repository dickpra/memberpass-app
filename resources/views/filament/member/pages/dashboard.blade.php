<x-filament-panels::page>
    @php
        $user = auth()->user();
        $settings = \App\Models\GeneralSetting::first();
        $now = \Carbon\Carbon::now();
        
        // LOGIC HITUNG HARGA PRORATED UNTUK TAMPILAN
        // (Ini hanya visual, perhitungan asli tetap di controller saat klik)
        $greenTier = \App\Models\MembershipTier::where('name', 'GreenCard')->first();
        $displayPrice = 0;
        $priceNote = '';

        if ($greenTier) {
            $basePrice = $greenTier->price;
            if ($now->month == 12) {
                // Desember = Full Price (Bonus)
                $displayPrice = $basePrice;
                $priceNote = "Early Bird " . ($now->year + 1);
            } else {
                // Jan-Nov = Prorated
                $daysInYear = $now->copy()->endOfYear()->dayOfYear;
                $remaining = $now->diffInDays($now->copy()->endOfYear()) + 1;
                $calc = ($remaining / $daysInYear) * $basePrice;
                $min = $basePrice / 12;
                $displayPrice = ceil(max($calc, $min) / 1000) * 1000;
                $priceNote = "Prorated (Sisa Tahun Ini)";
            }
        }
    @endphp

    <div class="membership-container">

        {{-- ALUR 1: USER BELUM MEMILIH PAKET (HANYA MUNCUL GREENCARD) --}}
        {{-- ALUR 1: USER BELUM MEMILIH PAKET --}}
        @if($user->status === 'registered')
            
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800">Choose Your Membership</h2>
                <p class="text-gray-500 mt-2">Bergabunglah dengan komunitas eksklusif kami.</p>
            </div>

            <div class="max-w-md mx-auto">
                @php
                    // REVISI LOGIC:
                    // Hanya ambil tier yang Aktif DAN BUKAN Invitation Only
                    // Jadi VIP Lifetime tidak akan pernah muncul disini.
                    $publicTier = \App\Models\MembershipTier::where('is_active', true)
                                    ->where('is_invitation_only', false) 
                                    ->first(); // Mengambil GreenCard
                    
                    // Logic Prorated (Visual Only)
                    $displayPrice = 0;
                    $priceNote = '';
                    
                    if ($publicTier) {
                        $basePrice = $publicTier->price;
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
                    <div class="p-4 bg-gray-100 text-center rounded text-gray-500">
                        Pendaftaran Membership sedang ditutup.
                    </div>
                @endif
            </div>

        {{-- ALUR 2: USER SEDANG MENUNGGU PEMBAYARAN --}}
        @elseif($user->status === 'waiting_payment')
             <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            {{-- Kiri: Info Tagihan --}}
            <div class="md:col-span-1 space-y-6">
                <div class="p-6 bg-white rounded-xl shadow-sm border border-gray-200">
                    <h2 class="text-lg font-bold mb-4 text-gray-800">Order Summary</h2>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-500">Package</span>
                        <span class="font-bold text-primary-600">{{ $user->membership_type }}</span>
                    </div>
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-gray-500">Total</span>
                        @php $payment = \App\Models\Payment::where('user_id', $user->id)->latest()->first(); @endphp
                        <span class="font-bold text-xl">{{ $settings->currency }} {{ number_format($payment->amount ?? 0) }}</span>
                    </div>
                    
                    <div class="flex flex-col gap-3 pt-4 border-t">
                         {{ $this->downloadInvoiceAction }}
                         {{ $this->cancelOrderAction }}
                    </div>
                </div>

                <div class="p-6 rounded-xl border bg-blue-50 border-blue-100 text-sm">
                     <h2 class="text-base font-bold mb-3 text-blue-900 border-b border-blue-200 pb-2">
                         Payment Details
                     </h2>
                     
                     <div class="mb-4">
                         <p class="text-xs text-blue-500 uppercase tracking-wider font-semibold">Bank Name</p>
                         <p class="font-bold text-lg text-gray-800">{{ $settings->bank_name }}</p>
                     </div>
                     
                     <div class="mb-4">
                         <p class="text-xs text-blue-500 uppercase tracking-wider font-semibold">Account Number</p>
                         <div class="flex items-center gap-2">
                            <p class="font-mono text-xl font-bold text-gray-900 bg-white px-2 py-1 rounded border border-blue-100 inline-block">
                                {{ $settings->bank_account_number }}
                            </p>
                         </div>
                     </div>

                     <div class="mb-4">
                         <p class="text-xs text-blue-500 uppercase tracking-wider font-semibold">Beneficiary Name</p>
                         <p class="font-bold text-gray-800">{{ $settings->bank_account_owner }}</p>
                     </div>

                     @if($settings->bank_swift_code || $settings->bank_city)
                     <div class="mt-4 pt-4 border-t border-blue-200">
                         <p class="text-xs font-bold text-blue-800 mb-2">INTERNATIONAL TRANSFER INFO</p>
                         
                         <div class="grid grid-cols-2 gap-2">
                             @if($settings->bank_swift_code)
                             <div>
                                 <p class="text-xs text-blue-500">SWIFT / BIC</p>
                                 <p class="font-mono font-bold">{{ $settings->bank_swift_code }}</p>
                             </div>
                             @endif

                             @if($settings->bank_city)
                             <div>
                                 <p class="text-xs text-blue-500">Bank Branch/City</p>
                                 <p class="font-bold">{{ $settings->bank_city }}</p>
                             </div>
                             @endif
                         </div>

                         @if($settings->organization_address)
                         <div class="mt-3">
                             <p class="text-xs text-blue-500">Beneficiary Address</p>
                             <p class="text-xs text-gray-700 leading-relaxed">{{ $settings->organization_address }}</p>
                         </div>
                         @endif
                     </div>
                     @endif
                </div>
            </div>

            {{-- Kanan: Form Upload --}}
            <div class="md:col-span-2 p-6 bg-white rounded-xl shadow-sm border border-gray-200">
                <h2 class="text-lg font-bold mb-4">Confirm Payment</h2>
                <x-filament-panels::form wire:submit="submitPayment">
                    {{ $this->form }}
                    <div class="mt-6 flex justify-end">
                        <x-filament::button type="submit" size="lg">
                            Submit Payment Proof
                        </x-filament::button>
                    </div>
                </x-filament-panels::form>
            </div>
        </div>


        {{-- ALUR 3: MEMBER SUDAH AKTIF (GREENCARD ATAU VIP) --}}
        @elseif($user->status === 'active')
            
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-800">Welcome Back, {{ $user->name }}!</h2>
                <p class="text-gray-500">Your membership is active.</p>
            </div>

            <div class="max-w-md mx-auto ">
                {{-- CEK APAKAH DIA VIP LIFETIME? --}}
                @if($user->membership_type === 'VIP Lifetime')
                    
                    <div class="pricing-card vip-lifetime">
                        <div class="badge-infinite">∞ LIFETIME PRIVILEGE</div>
                        <div class="card-header">
                            <h3>VIP ACCESS</h3>
                            <p class="mt-2 text-yellow-500 tracking-widest font-bold">MEMBER #{{ $user->member_id }}</p>
                            <p class="text-xs text-gray-400 mt-1">Auto-Renewed Annually</p>
                        </div>
                        <div class="card-body">
                            <ul class="features-list">
                                <li><x-heroicon-s-star /> Full Access System</li>
                                <li><x-heroicon-s-star /> Priority VVIP Support</li>
                                <li><x-heroicon-s-star /> Exclusive Merchandise</li>
                                <li><x-heroicon-s-calendar /> Valid until : {{ \Carbon\Carbon::parse($user->expiry_date)->translatedFormat('d F Y') }}</li>
                            </ul>

                            <div class="text-center mt-4 p-3 bg-white/5 rounded border border-white/10">
                                <p class="text-xs text-gray-400">Digital Status</p>
                                <p class="text-green-400 font-mono font-bold">ACTIVE • VERIFIED</p>
                            </div>
                        </div>
                    </div>

                @else
                    
                    <div class="pricing-card green" style="transform: none;">
                        <div class="card-header" style="padding: 1.5rem;">
                            <h3 style="font-size: 1.5rem;">GREENCARD</h3>
                            <p class="opacity-90 mt-1">{{ $user->member_id }}</p>
                        </div>
                        <div class="card-body">
                            <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-100">
                                <span class="text-gray-500">Status</span>
                                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-bold">Active</span>
                            </div>
                            <div class="flex items-center justify-between mb-6">
                                <span class="text-gray-500">Valid Until</span>
                                <span class="font-bold text-gray-800">
                                    {{ \Carbon\Carbon::parse($user->expiry_date)->translatedFormat('d F Y') }}
                                </span>
                            </div>
                            
                            {{-- Area QR Code bisa ditaruh sini --}}
                            <div class="bg-gray-100 h-32 rounded flex items-center justify-center text-gray-400 text-sm">
                                [ QR CODE AREA ]
                            </div>
                        </div>
                    </div>

                @endif
            </div>

        @else
            {{-- Status lain (Rejected / Waiting Verification) --}}
            {{-- Tampilkan alert standar --}}
            <div class="p-8 text-center bg-gray-50 rounded-xl border">
                 <h2 class="font-bold text-gray-700">Status: {{ ucfirst(str_replace('_', ' ', $user->status)) }}</h2>
            </div>
        @endif

    </div>
</x-filament-panels::page>
<x-filament-panels::page>

    {{-- CSS CUSTOM --}}
    <style>
        /* Grid Layout */
        .donation-grid { display: grid; grid-template-columns: repeat(1, minmax(0, 1fr)); gap: 1.5rem; }
        @media (min-width: 768px) { .donation-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1024px) { .donation-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }

        /* Card Styling */
        .custom-card {
            background: white; border: 1px solid #e5e7eb; border-radius: 1rem; overflow: hidden;
            display: flex; flex-direction: column; height: 100%; position: relative;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .dark .custom-card { background: #18181b; border-color: #27272a; }
        .custom-card:hover { transform: translateY(-4px); box-shadow: 0 12px 20px -8px rgba(0, 0, 0, 0.15); border-color: #22c55e; }

        /* Fallback Banner */
        .banner-placeholder {
            width: 100%; height: 12rem;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            display: flex; align-items: center; justify-content: center;
            border-bottom: 1px solid #f0f0f0;
        }
        .dark .banner-placeholder { background: #27272a; border-color: #3f3f46; }

        /* Badge Styling (Fix Bug Warna Putih) */
        .status-badge {
            position: absolute; top: 0.75rem; right: 0.75rem;
            background-color: rgba(0, 0, 0, 0.75);
            color: #ffffff !important;
            font-size: 0.7rem; font-weight: 800; text-transform: uppercase;
            padding: 0.35rem 0.6rem; border-radius: 0.375rem;
            z-index: 10; letter-spacing: 0.05em;
            backdrop-filter: blur(4px);
        }

        /* Typography & Spacing */
        .card-content { padding: 1.5rem; display: flex; flex-direction: column; flex-grow: 1; }
        .card-title { font-size: 1.25rem; font-weight: 800; color: #111827; margin-bottom: 0.75rem; line-height: 1.4; }
        .dark .card-title { color: #f3f4f6; }
        
        .card-desc {
            font-size: 0.9rem; color: #6b7280; line-height: 1.6; margin-bottom: 1.5rem;
            display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
        }
        .dark .card-desc { color: #9ca3af; }

        /* Bank Card */
        .bank-card {
            background: #f8fafc; border: 1px solid #e2e8f0; padding: 1rem; border-radius: 0.75rem; margin-bottom: 0.75rem;
        }
        .dark .bank-card { background: #27272a; border-color: #3f3f46; }
    </style>

    <div x-data="{ activeTab: @entangle('activeTab') }">

        {{-- TABS NAVIGASI --}}
        <div class="flex space-x-3 border-b border-gray-200 dark:border-gray-700 mb-8 pb-1">
            <button type="button" @click="activeTab = 'campaigns'" 
                :class="activeTab === 'campaigns' ? 'border-b-2 border-primary-600 text-primary-600' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                class="px-4 py-3 font-bold text-sm transition focus:outline-none">
                ❤️ Donate Now
            </button>
            <button type="button" @click="activeTab = 'history'" 
                :class="activeTab === 'history' ? 'border-b-2 border-primary-600 text-primary-600' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                class="px-4 py-3 font-bold text-sm transition focus:outline-none">
                📜 My History
            </button>
        </div>

        {{-- TAB 1: CAMPAIGNS LIST --}}
        <div x-show="activeTab === 'campaigns'" class="space-y-6">
            
            {{-- A. LIST PROGRAM --}}
            @if(!$selectedProgram)
                @if($programs->isEmpty())
                    <div class="flex flex-col items-center justify-center py-16">
                        {{-- <x-heroicon-o-information-circle class="w-12 h-12 text-gray-400 mb-4"/> --}}
                        <p class="text-gray-500 text-lg font-semibold mb-2">No donation programs available at the moment.</p>
                        <p class="text-gray-400 text-sm">Please check back later for new donation opportunities.</p>
                    </div>
                @else
                    <div class="donation-grid">
                        @foreach($programs as $program)
                            <div class="custom-card">
                                {{-- Banner --}}
                                <div class="relative">
                                    @if($program->banner_image)
                                        <div class="h-48 w-full">
                                            <img src="{{ asset('storage/' . $program->banner_image) }}" class="w-full h-full object-cover">
                                        </div>
                                    @else
                                        <div class="banner-placeholder">
                                            <div class="text-center">
                                                <x-heroicon-o-heart class="w-10 h-10 text-green-500 mx-auto mb-2 opacity-50"/>
                                                <span class="text-xs font-bold text-green-700 uppercase tracking-widest">WFIEd Donation</span>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="status-badge">
                                        {{ $program->target_amount ? 'Goal: $' . number_format($program->target_amount) : 'Open Donation' }}
                                    </div>
                                </div>

                                <div class="card-content">
                                    <h3 class="card-title">{{ $program->title }}</h3>
                                    <div class="card-desc">{!! strip_tags($program->description) !!}</div>
                                    <div class="mt-auto">
                                        <x-filament::button wire:click="selectProgram({{ $program->id }})" class="w-full font-bold" size="lg">
                                            Donate Now &rarr;
                                        </x-filament::button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif

            {{-- B. FORM DONASI --}}
            @if($selectedProgram)
                <div>
                    {{-- TOMBOL KEMBALI (FIXED: Pakai wire:click ke function PHP) --}}
                    <button type="button" 
                        wire:click="resetProgram"
                        class="mb-4 inline-flex items-center px-3 py-2 text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition shadow-sm border border-gray-200">
                        <x-heroicon-m-arrow-left class="w-4 h-4 mr-2"/>
                        Back to Campaign List
                    </button>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        
                        {{-- KOLOM KIRI: INFO --}}
                        <div class="lg:col-span-1 space-y-6">
                            
                            {{-- Detail Campaign --}}
                            <x-filament::section>
                                <div class="border-l-4 border-green-500 pl-4 py-1 bg-green-50 dark:bg-green-900/20 rounded-r">
                                    <span class="text-xs font-bold text-green-700 dark:text-green-400 uppercase tracking-wider">Selected Campaign</span>
                                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mt-1 leading-tight">
                                        {{ $selectedProgram->title }}
                                    </h2>
                                </div>
                                <div class="mt-4 text-sm text-gray-600 dark:text-gray-400 prose prose-sm max-h-40 overflow-y-auto">
                                    {!! $selectedProgram->description !!}
                                </div>
                            </x-filament::section>

                            {{-- Info Rekening --}}
                            <x-filament::section>
                                <x-slot name="heading">Transfer Destinations</x-slot>
                                
                                <div class="mb-2 text-xs text-gray-600 dark:text-gray-300 italic">
                                    Please choose <span class="font-bold">one</span> of the accounts below to make your transfer.
                                </div>

                                <div class="space-y-4 mt-4">
                                    @foreach($paymentMethods as $method)
                                        <div class="bank-card group hover:border-primary-500 transition">
                                            <div class="flex items-center justify-between mb-2 pb-2 border-b border-gray-200 dark:border-gray-600">
                                                <div class="flex items-center gap-2">
                                                    @if($method->logo)
                                                        <img src="{{ asset('storage/'.$method->logo) }}" class="h-5 w-auto object-contain">
                                                    @endif
                                                    <span class="font-bold text-sm text-gray-800 dark:text-gray-100">{{ $method->provider_name }}</span>
                                                </div>
                                                <span class="text-[10px] font-bold bg-gray-200 dark:bg-gray-700 px-2 py-0.5 rounded text-gray-600 dark:text-gray-300">
                                                    {{ $method->currency_code }}
                                                </span>
                                            </div>

                                            <div class="bg-white dark:bg-gray-800 p-2 rounded border border-gray-200 dark:border-gray-700 flex justify-between items-center mb-2">
                                                <span class="font-mono font-bold text-primary-700 dark:text-primary-400 text-sm select-all">
                                                    {{ $method->account_number }}
                                                </span>
                                                <button type="button" onclick="navigator.clipboard.writeText('{{ $method->account_number }}'); alert('Copied!')">
                                                    <x-heroicon-m-clipboard class="w-4 h-4 text-gray-400 hover:text-primary-600"/>
                                                </button>
                                            </div>

                                            <div class="text-[11px] text-gray-600 dark:text-gray-400 mt-2 pt-2 border-t border-gray-200 dark:border-gray-700 border-dashed space-y-1.5">
                                                
                                                @if($method->method_type === 'bank_transfer')
                                                    
                                                    {{-- 1. Holder Name --}}
                                                    <div>
                                                        <span class="font-bold text-gray-400 uppercase text-[10px] tracking-wider">Account Holder:</span>
                                                        <div class="font-bold text-gray-800 dark:text-gray-200 text-sm">
                                                            {{ $method->account_owner }}
                                                        </div>
                                                    </div>

                                                    {{-- Grid Kecil untuk SWIFT & TAX (Biar rapi tapi rapat) --}}
                                                    <div class="grid grid-cols-2 gap-2">
                                                        @if($method->swift_code)
                                                            <div>
                                                                <span class="font-bold text-gray-400 uppercase text-[10px]">SWIFT / BIC:</span>
                                                                <div class="font-mono font-bold text-gray-700 dark:text-gray-300 select-all">
                                                                    {{ $method->swift_code }}
                                                                </div>
                                                            </div>
                                                        @endif

                                                        @if($method->tax_number)
                                                            <div>
                                                                <span class="font-bold text-gray-400 uppercase text-[10px]">Tax ID:</span>
                                                                <div class="font-mono font-bold text-gray-700 dark:text-gray-300 select-all">
                                                                    {{ $method->tax_number }}
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    {{-- 3. Branch Info --}}
                                                    @if($method->bank_branch || $method->bank_city)
                                                        <div>
                                                            <span class="font-bold text-gray-400 uppercase text-[10px]">Bank Branch:</span>
                                                            <div class="text-gray-700 dark:text-gray-300 leading-tight">
                                                                {{ $method->bank_branch }}{{ $method->bank_branch && $method->bank_city ? ',' : '' }} {{ $method->bank_city }}
                                                            </div>
                                                        </div>
                                                    @endif

                                                    {{-- 4. Address (Dalam Box agar terpisah jelas) --}}
                                                    @if($method->owner_address)
                                                        <div class="mt-1">
                                                            <span class="font-bold text-gray-400 uppercase text-[10px]">Registered Address:</span>
                                                            <div class="bg-gray-50 dark:bg-gray-800 p-1.5 rounded border border-gray-100 dark:border-gray-700 text-gray-600 dark:text-gray-400 leading-snug select-all mt-0.5">
                                                                {{ $method->owner_address }}
                                                            </div>
                                                        </div>
                                                    @endif

                                                @elseif($method->method_type === 'paypal')
                                                    <div class="bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 p-2 rounded text-xs border border-amber-100 dark:border-amber-800">
                                                        <span class="font-bold">Note:</span> Please select "Friends & Family" option.
                                                    </div>
                                                @endif
                                            </div>
                                            {{-- FITUR BARU: INSTRUKSI KHUSUS --}}
                                            @if($method->instructions)
                                                <div class="mt-2 p-2 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-100 dark:border-yellow-900/30 rounded text-xs text-yellow-800 dark:text-yellow-200">
                                                    <div class="flex gap-1">
                                                        <x-heroicon-m-information-circle class="w-4 h-4 shrink-0"/>
                                                        <span>{{ $method->instructions }}</span>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </x-filament::section>
                        </div>

                        {{-- KOLOM KANAN: FORM --}}
                        <div class="lg:col-span-2">
                            <x-filament::section>
                                <x-slot name="heading">Confirm Payment</x-slot>
                                <x-slot name="description">Fill in this form after you have made the transfer.</x-slot>

                                <form wire:submit="create" class="space-y-6 mt-4">
                                    {{ $this->form }}
                                    <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-gray-700">
                                        <x-filament::button type="submit" size="lg" color="success" icon="heroicon-m-check-circle">
                                            Submit Confirmation
                                        </x-filament::button>
                                    </div>
                                </form>
                            </x-filament::section>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- TAB 2: HISTORY --}}
        <div x-show="activeTab === 'history'">
            <x-filament::section>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 font-bold uppercase text-xs">
                            <tr>
                                <th class="px-6 py-4 rounded-tl-lg">Date</th>
                                <th class="px-6 py-4">Program</th>
                                <th class="px-6 py-4">Amount</th>
                                <th class="px-6 py-4 rounded-tr-lg">Status</th>
                                <th class="px-6 py-4 rounded-tr-lg">Receipt</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($myDonations as $donation)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                    <td class="px-6 py-4 text-gray-500">{{ $donation->created_at->format('d M Y') }}</td>
                                    <td class="px-6 py-4 font-bold text-gray-800 dark:text-gray-200">{{ $donation->program->title }}</td>
                                    <td class="px-6 py-4 font-mono font-bold text-primary-600">
                                        {{ $donation->currency }} {{ number_format($donation->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($donation->status === 'approved')
                                            <x-filament::badge color="success" icon="heroicon-m-check-badge">Verified</x-filament::badge>
                                        @elseif($donation->status === 'rejected')
                                            <div class="flex flex-col items-start">
                                                <x-filament::badge color="danger" icon="heroicon-m-x-circle">Rejected</x-filament::badge>
                                                <span class="text-[10px] text-red-500 mt-1 max-w-[150px] leading-tight">{{ $donation->admin_note }}</span>
                                            </div>
                                        @else
                                            <x-filament::badge color="warning" icon="heroicon-m-clock">Pending</x-filament::badge>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($donation->status === 'approved')
                                            {{-- TOMBOL AKTIF --}}
                                            <a href="{{ route('donation.receipt', $donation->id) }}" target="_blank" 
                                            class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-800 font-bold text-xs border border-primary-200 bg-primary-50 px-3 py-1.5 rounded transition hover:shadow-sm">
                                                <x-heroicon-m-document-arrow-down class="w-4 h-4"/> 
                                                Download PDF
                                            </a>
                                        @else
                                            {{-- TOMBOL DISABLED --}}
                                            <span class="text-gray-300 flex items-center justify-center gap-1 cursor-not-allowed opacity-50 text-xs">
                                                <x-heroicon-m-clock class="w-4 h-4"/> 
                                                Verifying
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-400 italic">
                                        No donation history yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        </div>

    </div>
</x-filament-panels::page>
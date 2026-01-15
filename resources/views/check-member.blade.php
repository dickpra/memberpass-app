<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Member Status</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-6 bg-gradient-to-br from-slate-100 to-slate-200">

    {{-- HEADER --}}
    <div class="text-center mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Check Membership</h1>
        <p class="text-slate-500 mt-2">Verifikasi status keanggotaan secara real-time.</p>
    </div>

    {{-- CARD UTAMA --}}
    <div class="w-full max-w-md bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
        
        {{-- FORM PENCARIAN --}}
        <div class="p-8 pb-6">
            <form action="{{ route('check.member') }}" method="GET" class="relative">
                <label for="search" class="block text-sm font-bold text-slate-700 mb-2">Member ID / Email</label>
                <div class="relative">
                    <input type="text" name="search" id="search" 
                        value="{{ $search }}" 
                        placeholder="Contoh: MEM-2024-001" 
                        class="w-full pl-4 pr-12 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition font-mono font-medium text-slate-800"
                        required>
                    <button type="submit" class="absolute right-2 top-2 bottom-2 bg-slate-900 hover:bg-green-600 text-white p-2 rounded-lg transition">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </button>
                </div>
            </form>
        </div>

        {{-- HASIL PENCARIAN --}}
        @if($search)
            <div class="border-t border-slate-100 bg-slate-50/50 p-8 min-h-[200px] flex items-center justify-center">
                
                @if($member)
                    {{-- JIKA MEMBER DITEMUKAN --}}
                    <div class="w-full text-center">
                        
                        {{-- Indikator Status --}}
                        @if($status === 'ACTIVE')
                            <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-green-100 text-green-700 rounded-full text-sm font-bold mb-4 animate-pulse">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                MEMBERSHIP ACTIVE
                            </div>
                        @elseif($status === 'BANNED')
                            <div class="inline-block px-4 py-1.5 bg-red-100 text-red-700 rounded-full text-sm font-bold mb-4">
                                ⛔ ACCOUNT BANNED
                            </div>
                        @else
                            <div class="inline-block px-4 py-1.5 bg-gray-200 text-gray-600 rounded-full text-sm font-bold mb-4">
                                INACTIVE / EXPIRED
                            </div>
                        @endif

                        {{-- Foto Profil (Inisial) --}}
                        <div class="w-20 h-20 bg-slate-200 rounded-full mx-auto flex items-center justify-center text-2xl font-bold text-slate-500 mb-4 shadow-inner">
                            {{ substr($member->name, 0, 1) }}
                        </div>

                        {{-- Nama & Tipe --}}
                        <h2 class="text-xl font-bold text-slate-900">{{ $member->name }}</h2>
                        <p class="text-slate-500 text-sm font-medium uppercase tracking-wide mt-1">{{ $member->membership_type }}</p>

                        {{-- Detail Grid --}}
                        <div class="grid grid-cols-2 gap-4 mt-6 text-left bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                            <div>
                                <p class="text-[10px] uppercase text-slate-400 font-bold">Member ID</p>
                                <p class="font-mono text-sm font-bold text-slate-800">{{ $member->member_id ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase text-slate-400 font-bold">Valid Until</p>
                                @if($member->membership_type === 'VIP Lifetime')
                                    <p class="font-bold text-sm text-yellow-600 flex items-center gap-1">
                                        ∞ LIFETIME
                                    </p>
                                @elseif($member->expiry_date)
                                    <p class="font-mono text-sm font-bold {{ $status === 'ACTIVE' ? 'text-green-600' : 'text-red-500' }}">
                                        {{ \Carbon\Carbon::parse($member->expiry_date)->format('d M Y') }}
                                    </p>
                                @else
                                    <p class="text-sm text-slate-500">-</p>
                                @endif
                            </div>
                        </div>

                        {{-- Tombol Login (Opsional) --}}
                        <div class="mt-6">
                            <a href="{{ url('/member/login') }}" class="text-sm text-green-600 font-bold hover:underline">
                                Login to Dashboard &rarr;
                            </a>
                        </div>
                    </div>

                @else
                    {{-- JIKA TIDAK DITEMUKAN --}}
                    <div class="text-center">
                        <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8 text-red-500">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800">Member Not Found</h3>
                        <p class="text-slate-500 text-sm mt-1 max-w-xs mx-auto">
                            Data tidak ditemukan. Pastikan Member ID atau Email yang Anda masukkan sudah benar.
                        </p>
                    </div>
                @endif
            </div>
        @else
            {{-- STATE AWAL (BELUM CARI) --}}
            <div class="border-t border-slate-100 bg-slate-50/50 p-12 text-center text-slate-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto mb-3 opacity-50">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <p class="text-sm">Masukkan ID Member atau Email untuk memulai pencarian.</p>
            </div>
        @endif

    </div>

    {{-- FOOTER --}}
    <div class="mt-8 text-center text-xs text-slate-400">
        &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
    </div>

</body>
</html>
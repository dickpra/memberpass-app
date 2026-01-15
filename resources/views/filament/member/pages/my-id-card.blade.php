<x-filament-panels::page>
    {{-- LIBRARIES --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    {{-- FONTS --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;800;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">

    {{-- CUSTOM CSS --}}
    <style>
        /* ========== BASE CARD ========== */
        .id-card-wrapper {
            width: 500px; height: 315px;
            position: relative; overflow: visible;
            font-family: "Inter", sans-serif;
            box-sizing: border-box;
        }

        .card-inner {
            width: 100%; height: 100%;
            border-radius: 16px; overflow: hidden;
            box-sizing: border-box; padding: 20px;
            display: flex; flex-direction: column; justify-content: space-between;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
            box-shadow: 0 18px 40px rgba(2,6,23,0.25), inset 0 0 0 1px rgba(255,255,255,0.02);
            position: relative; background-color: white;
        }

        /* ================= DECOR ================= */
        .diagonal {
            position: absolute; width: 260pt; height: 260pt;
            background: linear-gradient(135deg, rgba(255,255,255,0.18), rgba(255,255,255,0.04));
            transform: rotate(25deg); top: -140pt; right: -100pt;
            pointer-events: none; z-index: 1;
        }
        .diagonal--small {
            position: absolute; width: 180pt; height: 180pt;
            background: linear-gradient(135deg, rgba(0,0,0,0.06), rgba(0,0,0,0.015));
            transform: rotate(18deg); bottom: -80pt; left: -60pt;
            pointer-events: none; z-index: 1;
        }

        /* ========== THEMES ========== */
        .theme-vip {
            background: radial-gradient(circle at 10% 10%, rgba(30,41,59,0.45), transparent 20%),
                        linear-gradient(180deg,#020617 0%, #071024 60%, #000 100%);
            color: #f7f1d1; border: 1px solid rgba(255,255,255,0.03);
        }
        .vip-accent { color: #f3c24a; font-weight: 700; letter-spacing: .06em; }

        .theme-green {
            background: radial-gradient(circle at top right, rgba(15, 127, 83, 0.06), transparent 55%),
                        linear-gradient(180deg, #dbe6df 0%, #cddbd2 100%);
            color: #0f172a; border: 1px solid #c2d2c8;
        }
        .theme-green::after {
            content:""; position:absolute; inset:0; border-radius:16px;
            background: radial-gradient(circle at bottom left, rgba(0,0,0,0.05), transparent 40%);
            pointer-events:none;
        }
        .green-accent { color: #0f6d49; font-weight: 700; letter-spacing: .02em; }
        .theme-green .bottom-accent {
            height:4px; width:100%; border-radius:4px; margin-top:6px;
            background: rgba(16,185,129,0.85); opacity: .95;
        }

        /* ========== SVG CONTAINERS ========== */
        .brand { display:flex; gap:12px; align-items:center; }
        
        .brand-logo {
            width:36px; height:36px; border-radius:10px;
            background: linear-gradient(135deg,#fbbf24,#d97706);
            box-shadow: 0 6px 22px rgba(18,18,18,0.25);
            display: block; padding: 0; margin: 0; overflow: hidden;
        }
        .badge {
            width: 80px; height: 26px; border-radius:999px; 
            background: rgba(243,194,74,0.07); border: 1px solid rgba(243,194,74,0.25); 
            padding: 0; margin: 0; display: block; overflow: hidden;
        }
        .card-stamp-box {
            width: 90px; height: 76px; background: transparent;
            border-radius: 8px; padding: 0; margin: 0; display: block; overflow: hidden;
        }

        .svg-centered { width: 100%; height: 100%; display: block; }

        .info-svg-container {
            width: 100%; height: 60px; display: block; margin-top: 10px;
        }
        
        .meta-svg-container {
            width: 100%; height: 35px; display: block; margin-top: 8px;
        }

        /* Barcode & Back */
        .barcode-wrap { width:100%; display:flex; justify-content:center; margin-top:8px; }
        .barcode-box {
            background: #ffffff; padding:6px 8px; border-radius:10px;
            box-shadow: 0 6px 18px rgba(2,6,23,0.15);
            width: 100%; max-width:420px;
            display:flex; justify-content:center; align-items:center;
        }
        .barcode-img { width:100%; height:46px; object-fit:contain; display:block; }
        .back-text-svg { width: 100%; height: 20px; display: block; margin-bottom: 4px; }
        .back-id-svg { width: 100%; height: 20px; display: block; margin-top: 8px; }
        .card-footnote { font-size:9px; opacity:.6; margin-top:8px; text-align:center; line-height:1.2; }

        #__wf_print_clone_wrapper { position: fixed; top: -99999px; left: -99999px; width: auto; height: auto; overflow: visible; pointer-events: none; z-index: 2147483647; }
        .print-safe .card-inner { box-shadow: none !important; }
        .print-safe .diagonal, .print-safe .diagonal--small { opacity: 0.85; }
        .print-safe { padding: 0 !important; background: transparent !important; }
        
        @media (max-width: 900px) { .id-card-wrapper { width: 420px; height: 264px; } }
    </style>

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

    <div class="grid lg:grid-cols-2 gap-8 items-start justify-center">
        <div class="space-y-8">
            <h2 class="text-xl font-bold text-gray-800">Card Preview</h2>

            {{-- FRONT --}}
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 block" style="margin-bottom: 16px;">Front Side (Identitas)</span>
                <div style="margin-bottom: 20px;"></div>
                
                <div id="card-front" class="id-card-wrapper print-safe">
                    <div class="card-inner {{ $user->membership_type === 'VIP Lifetime' ? 'theme-vip' : 'theme-green' }}">
                        <div class="diagonal" aria-hidden="true"></div>
                        <div class="diagonal--small" aria-hidden="true"></div>

                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <div class="brand">
                                <div class="brand-logo">
                                    <svg class="svg-centered" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
                                        <text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" fill="#0b1220" font-family="'Inter', sans-serif" font-weight="900" font-size="14">WF</text>
                                    </svg>
                                </div>
                                {{-- BRAND TEXT SVG --}}
                                <div style="width: 120px; height: 36px;">
                                    <svg class="svg-centered" viewBox="0 0 120 36" xmlns="http://www.w3.org/2000/svg">
                                        <text x="0" y="14" fill="{{ $user->membership_type === 'VIP Lifetime' ? '#f7f1d1' : '#0f172a' }}" font-family="'Inter', sans-serif" font-weight="800" font-size="13">WFIEd</text>
                                        <text x="0" y="28" fill="{{ $user->membership_type === 'VIP Lifetime' ? '#f7f1d1' : '#0f172a' }}" font-family="'Inter', sans-serif" font-size="11" opacity="0.8">
                                            {{ $user->membership_type === 'VIP Lifetime' ? 'Exclusive Member' : 'Official Membership' }}
                                        </text>
                                    </svg>
                                </div>
                            </div>

                            @if($user->membership_type === 'VIP Lifetime')
                                <div class="badge">
                                    <svg class="svg-centered" viewBox="0 0 80 26" xmlns="http://www.w3.org/2000/svg">
                                        <text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" fill="#f3c24a" font-family="'Inter', sans-serif" font-weight="700" font-size="11" letter-spacing="1.2">LIFETIME</text>
                                    </svg>
                                </div>
                            @else
                                <div style="text-align:right;">
                                    <div style="width: 100px; height: 20px;">
                                        <svg class="svg-centered" viewBox="0 0 100 20" xmlns="http://www.w3.org/2000/svg">
                                            <text x="100%" y="50%" dominant-baseline="middle" text-anchor="end" fill="#0f6d49" font-family="'Inter', sans-serif" font-weight="700" font-size="11">GreenCard</text>
                                        </svg>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div style="display:flex; gap:18px; align-items:center; margin-top:5px;">
                            <div style="flex:1;">
                                {{-- NAMA & ID SVG --}}
                                <div class="info-svg-container">
                                    <svg class="svg-centered" viewBox="0 0 300 60" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMinYMid meet">
                                        <text x="0" y="24" 
                                              font-family="'Inter', sans-serif" 
                                              font-weight="800" 
                                              font-size="26"
                                              fill="{{ $user->membership_type === 'VIP Lifetime' ? '#f3c24a' : '#0f172a' }}"
                                              style="{{ $user->membership_type === 'VIP Lifetime' ? 'text-shadow: 0px 0px 10px rgba(243,194,74,0.5);' : '' }}">
                                            {{ $user->name }}
                                        </text>
                                        <text x="0" y="48" 
                                              font-family="'Share Tech Mono', monospace" 
                                              font-size="14"
                                              letter-spacing="1"
                                              fill="{{ $user->membership_type === 'VIP Lifetime' ? '#f3c24a' : '#145a3a' }}">
                                            {{ $user->member_id }}
                                        </text>
                                    </svg>
                                </div>

                                {{-- PERBAIKAN: 3 KOLOM INFO (JOINED - VALID THRU - STATUS) --}}
                                <div class="meta-svg-container">
                                    {{-- Viewbox diperlebar agar muat 3 kolom --}}
                                    <svg class="svg-centered" viewBox="0 0 320 35" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMinYMid meet">
                                        
                                        <text x="0" y="10" font-family="'Inter', sans-serif" font-size="10" fill="{{ $user->membership_type === 'VIP Lifetime' ? '#fff' : '#0f172a' }}" opacity="0.7">Joined</text>
                                        <text x="0" y="24" font-family="'Inter', sans-serif" font-weight="700" font-size="12" fill="{{ $user->membership_type === 'VIP Lifetime' ? '#fff' : '#0f172a' }}" opacity="{{ $user->membership_type === 'VIP Lifetime' ? '0.9' : '1' }}">
                                            {{ \Carbon\Carbon::parse($user->join_date)->translatedFormat('M Y') }}
                                        </text>

                                        <text x="100" y="10" font-family="'Inter', sans-serif" font-size="10" fill="{{ $user->membership_type === 'VIP Lifetime' ? '#fff' : '#0f172a' }}" opacity="0.7">Valid Thru</text>
                                        <text x="100" y="24" font-family="'Inter', sans-serif" font-weight="700" font-size="12" fill="{{ $user->membership_type === 'VIP Lifetime' ? '#fff' : '#0f172a' }}" opacity="{{ $user->membership_type === 'VIP Lifetime' ? '0.9' : '1' }}">
                                            {{ $user->membership_type === 'VIP Lifetime' ? 'LIFETIME' : (\Carbon\Carbon::parse($user->expiry_date)->translatedFormat('d M Y')) }}
                                            {{-- {{ $user->membership_type === 'VIP Lifetime' ? 'LIFETIME' : \Carbon\Carbon::parse($user->expiry_date)->translatedFormat('M Y') }} --}}
                                        </text>

                                        <text x="210" y="10" font-family="'Inter', sans-serif" font-size="10" fill="{{ $user->membership_type === 'VIP Lifetime' ? '#fff' : '#0f172a' }}" opacity="0.7">Status</text>
                                        @php
                                            // Tentukan status aktif/nonaktif berdasarkan expiry_date
                                            $isExpired = false;
                                            if ($user->expiry_date && $user->membership_type !== 'VIP Lifetime') {
                                                $isExpired = \Carbon\Carbon::parse($user->expiry_date)->lt(\Carbon\Carbon::now());
                                            }
                                        @endphp
                                        <text x="210" y="24" font-family="'Inter', sans-serif" font-weight="700" font-size="12"
                                            fill="
                                                @if($user->membership_type === 'VIP Lifetime')
                                                    #34d399
                                                @elseif($user->membership_type !== 'VIP Lifetime' && $isExpired)
                                                    #ef4444
                                                @elseif($user->status === 'expired' || $user->status === 'inactive')
                                                    #ef4444
                                                @else
                                                    #22c55e
                                                @endif
                                            ">
                                            @if($user->membership_type !== 'VIP Lifetime' && $isExpired)
                                                INACTIVE
                                            @elseif($user->status === 'expired' || $user->status === 'inactive')
                                                INACTIVE
                                            @else
                                                {{ $user->membership_type === 'VIP Lifetime' ? 'VIP ACTIVE' : 'ACTIVE' }}
                                            @endif
                                        </text>
                                    </svg>
                                </div>
                            </div>

                            <div style="flex:0 0 auto; text-align:right;">
                                <div class="card-stamp-box" aria-hidden="true">
                                    <svg class="svg-centered" viewBox="0 0 90 76" xmlns="http://www.w3.org/2000/svg">
                                        @if($user->membership_type === 'VIP Lifetime')
                                            <rect x="2" y="10" width="86" height="56" rx="4" stroke="#f3c24a" stroke-width="2" fill="none" opacity="0.8"/>
                                            <text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" fill="#f3c24a" font-family="'Inter', sans-serif" font-weight="900" font-size="38" letter-spacing="-1">VIP</text>
                                        @else
                                            <rect x="2" y="18" width="86" height="40" rx="4" stroke="#145a3a" stroke-width="2" fill="none" opacity="0.3"/>
                                            <text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" fill="#145a3a" font-family="'Inter', sans-serif" font-weight="800" font-size="16" letter-spacing="1">MEMBER</text>
                                        @endif
                                    </svg>
                                </div>
                            </div>
                        </div>

                        @if($user->membership_type === 'VIP Lifetime')
                            <div style="height:6px; width:100%; border-radius:6px; margin-top:6px; background: linear-gradient(90deg,#b37e21,#f3c24a,#b37e21);"></div>
                        @else
                            <div class="bottom-accent"></div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- BACK --}}
            <div style="margin-top: 36px;">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 block" style="margin-bottom: 16px;">Back Side (Barcode Scanner)</span>
                <div style="margin-bottom: 20px;"></div>
                <div id="card-back" class="id-card-wrapper print-safe">
                    <div class="card-inner {{ $user->membership_type === 'VIP Lifetime' ? 'theme-vip' : 'theme-green' }}">
                        <div class="diagonal" aria-hidden="true"></div>
                        <div style="width:100%; text-align:center;">
                            {{-- BACK TITLE SVG --}}
                            <div class="back-text-svg">
                                <svg class="svg-centered" viewBox="0 0 200 20" xmlns="http://www.w3.org/2000/svg">
                                    <text x="50%" y="55%" dominant-baseline="middle" text-anchor="middle" font-family="'Inter', sans-serif" font-size="12" font-weight="700" fill="{{ $user->membership_type === 'VIP Lifetime' ? '#f3c24a' : '#0f6d49' }}">
                                        Scan for Verification
                                    </text>
                                </svg>
                            </div>
                        </div>
                        <div class="barcode-wrap">
                            <div class="barcode-box">
                                <img id="barcode-img" class="barcode-img" alt="barcode" crossorigin="anonymous">
                            </div>
                        </div>
                        
                        {{-- BACK ID SVG --}}
                        <div class="back-id-svg">
                             <svg class="svg-centered" viewBox="0 0 200 20" xmlns="http://www.w3.org/2000/svg">
                                <text x="50%" y="55%" dominant-baseline="middle" text-anchor="middle" font-family="'Share Tech Mono', monospace" font-size="11" letter-spacing="1.4" fill="{{ $user->membership_type === 'VIP Lifetime' ? '#f3c24a' : '#145a3a' }}">
                                    {{ $user->member_id }}
                                </text>
                            </svg>
                        </div>

                        <div class="card-footnote">
                            This card identifies the holder as a member of WFIEd. Non-transferable.
                            @if($user->membership_type === 'VIP Lifetime')
                                <strong style="color:#f3c24a; display:block; margin-top:4px;">PREMIUM LIFETIME SUPPORT ENABLED</strong>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ACTION / DOWNLOAD --}}
        <div class="lg:sticky lg:top-10 space-y-6">
            <div class="p-6 rounded-2xl border border-emerald-100 shadow-sm" style="background: linear-gradient(180deg,#f0f6f3,#e6efea);">
                <h3 class="font-bold text-gray-800 text-lg mb-1">Download Card</h3>
                <p class="text-sm text-gray-600 mb-5">Unduh kartu digital untuk keperluan cetak & arsip.</p>
                <div class="grid gap-3">
                    <button type="button" onclick="downloadPdf()" class="w-full rounded-xl px-4 py-3 font-semibold flex items-center justify-between border border-red-200 text-red-800 shadow-sm bg-white hover:bg-red-50 transition transform hover:-translate-y-0.5">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-document-arrow-down class="w-5 h-5"/>
                            <span>Download Full PDF</span>
                        </div>
                        <span class="text-xs font-bold border border-red-200 px-2 py-0.5 rounded bg-red-50">BEST</span>
                    </button>
                    <div class="border-t border-gray-200 my-1"></div>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" onclick="downloadPng('card-front','Front')" class="rounded-lg px-2 py-3 text-sm font-semibold text-center border border-emerald-200 text-emerald-800 bg-white/70 hover:bg-white transition">Download Front</button>
                        <button type="button" onclick="downloadPng('card-back','Back')" class="rounded-lg px-2 py-3 text-sm font-semibold text-center border border-emerald-200 text-emerald-800 bg-white/70 hover:bg-white transition">Download Back</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- offscreen clone wrapper --}}
    <div id="__wf_print_clone_wrapper" aria-hidden="true"></div>

    <script>
    const MEMBER_ID = @json($user->member_id);

    // Init preview barcode
    document.addEventListener('DOMContentLoaded', () => {
        try {
            const img = document.getElementById('barcode-img');
            if (img) {
                JsBarcode(img, MEMBER_ID, {
                    format: "CODE128", lineColor: "#000", width: 1.6, height: 48,
                    displayValue: false, background: "#ffffff", margin: 0
                });
            }
        } catch (e) { console.warn('JsBarcode preview error', e); }
    });

    function waitForImages(root, timeout = 5000) {
        const imgs = Array.from(root.querySelectorAll('img'));
        const promises = imgs.map(img => new Promise(resolve => {
            if (img.complete && img.naturalWidth !== 0) return resolve();
            const onFinish = () => resolve();
            img.addEventListener('load', onFinish, { once: true });
            img.addEventListener('error', onFinish, { once: true });
        }));
        const fontsPromise = (document.fonts && document.fonts.ready) ? document.fonts.ready : Promise.resolve();
        const timeoutPromise = new Promise(resolve => setTimeout(resolve, timeout));
        return Promise.race([ Promise.all(promises).then(() => fontsPromise), timeoutPromise ]);
    }

    function rewriteIds(node, suffix = '-clone') {
        const nodesWithId = node.querySelectorAll('[id]');
        nodesWithId.forEach(el => { el.id = el.id + suffix; });
        if (node.id) node.id = node.id + suffix;
    }

    async function generateCanvas(elementId) {
        const original = document.getElementById(elementId);
        if (!original) throw new Error('Element not found: ' + elementId);

        const clone = original.cloneNode(true);
        const wrapper = document.getElementById('__wf_print_clone_wrapper');
        wrapper.innerHTML = '';
        wrapper.appendChild(clone);

        clone.style.width = '500px';
        clone.style.height = '315px';
        clone.style.boxSizing = 'border-box';
        clone.style.position = 'relative';
        clone.style.transform = 'none';
        clone.style.margin = '0';
        clone.style.padding = '0';
        clone.style.backgroundColor = window.getComputedStyle(original).backgroundColor || '#ffffff';

        rewriteIds(clone, '-clone');

        clone.querySelectorAll('img').forEach(img => {
            try { img.setAttribute('crossorigin', 'anonymous'); } catch(e){}
        });

        const clonedBarcode = clone.querySelector('#barcode-img-clone');
        if (clonedBarcode) {
            try {
                JsBarcode(clonedBarcode, MEMBER_ID, {
                    format: "CODE128", lineColor: "#000", width: 1.6, height: 48,
                    displayValue: false, background: "#ffffff", margin: 0
                });
            } catch (e) {}
        }

        try { await waitForImages(clone, 6000); } catch(e){}

        const scale = 3;

        const canvas = await html2canvas(clone, {
            scale: scale,
            useCORS: true,
            allowTaint: false,
            backgroundColor: null,
            logging: false,
            width: 500,
            height: 315,
            scrollY: -window.scrollY 
        });

        wrapper.innerHTML = '';
        return canvas;
    }

    async function downloadPng(elementId, sideName) {
        try {
            const canvas = await generateCanvas(elementId);
            const a = document.createElement('a');
            a.download = `WFIED-${sideName}-${MEMBER_ID}.png`;
            a.href = canvas.toDataURL('image/png');
            a.click();
        } catch (err) {
            console.error(err);
            alert('Gagal generate PNG.');
        }
    }

    async function downloadPdf() {
        try {
            const { jsPDF } = window.jspdf;
            const CARD_W = 85.6;
            const CARD_H = 54;

            const doc = new jsPDF({
                orientation: 'landscape',
                unit: 'mm',
                format: [CARD_W, CARD_H]
            });

            const front = await generateCanvas('card-front');
            doc.addImage(front.toDataURL('image/png'), 'PNG', 0, 0, CARD_W, CARD_H);

            doc.addPage([CARD_W, CARD_H], 'landscape');
            const back = await generateCanvas('card-back');
            doc.addImage(back.toDataURL('image/png'), 'PNG', 0, 0, CARD_W, CARD_H);

            doc.save(`ID-Card-${MEMBER_ID}.pdf`);
        } catch (e) {
            console.error(e);
            alert('PDF gagal dibuat');
        }
    }
    </script>
</x-filament-panels::page>
<x-filament-panels::page>
    {{-- LIBRARIES --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>

    {{-- FONTS (CANVAS-SAFE) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">

    {{-- CUSTOM CSS --}}
    <style>
        /* ========== BASE CARD ========== */
        .id-card-wrapper {
            width: 500px;
            height: 315px;               /* Ratio ~1.58 (CR80) */
            position: relative;
            overflow: visible;           /* canvas-safe */
            font-family: "Inter", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
        }

        .card-inner {
            width: 100%;
            height: 100%;
            border-radius: 16px;
            overflow: hidden;            /* clip decorative children but not the wrapper */
            box-sizing: border-box;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            box-shadow:
                0 18px 40px rgba(2,6,23,0.25),
                inset 0 0 0 1px rgba(255,255,255,0.02);
            position: relative; /* for decorative absolute elements */
        }

        /* ================= DECOR ================= */
        .diagonal {
            position: absolute;
            width: 260pt;
            height: 260pt;
            background: linear-gradient(
                135deg,
                rgba(255,255,255,0.18),
                rgba(255,255,255,0.04)
            );
            transform: rotate(25deg);
            top: -140pt;
            right: -100pt;
            pointer-events: none;
            z-index: 1;
        }

        .diagonal--small {
            position: absolute;
            width: 180pt;
            height: 180pt;
            background: linear-gradient(
                135deg,
                rgba(0,0,0,0.06),
                rgba(0,0,0,0.015)
            );
            transform: rotate(18deg);
            bottom: -80pt;
            left: -60pt;
            pointer-events: none;
            z-index: 1;
        }


        /* ========== VIP THEME (black + gold) ========== */
        .theme-vip {
            background:
                radial-gradient(circle at 10% 10%, rgba(30,41,59,0.45), transparent 20%),
                linear-gradient(180deg,#020617 0%, #071024 60%, #000 100%);
            color: #f7f1d1;
            border: 1px solid rgba(255,255,255,0.03);
        }

        .vip-accent {
            color: #f3c24a;
            font-weight: 700;
            letter-spacing: .06em;
        }

        .theme-vip::after{
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 16px;
            pointer-events: none;
            box-shadow: 0 0 40px rgba(243,194,74,0.04) inset;
            z-index: 0;
        }

        /* ========== GREEN THEME (muted / calmer) ========== */
        /* made less saturated / softer */
        /* ========== GREEN THEME – MUTED / SAGE ========== */
        /* ========== GREEN THEME – DEEP SAGE (NO WHITE) ========== */
.theme-green {
    background:
        radial-gradient(
            circle at top right,
            rgba(15, 127, 83, 0.06),
            transparent 55%
        ),
        linear-gradient(
            180deg,
            #dbe6df 0%,
            #cddbd2 100%
        );
    color: #0f172a;
    border: 1px solid #c2d2c8;
}
.theme-green::after {
    content:"";
    position:absolute;
    inset:0;
    border-radius:16px;
    background:
        radial-gradient(circle at bottom left, rgba(0,0,0,0.05), transparent 40%);
    pointer-events:none;
}



        /* calmer green accent */
        .green-accent {
            color: #0f6d49; /* lebih gelap, lebih dewasa */
            font-weight: 700;
            letter-spacing: .02em;
        }


        /* slightly more muted status/line colors */
        .theme-green .bottom-accent {
            height:4px;
            width:100%;
            border-radius:4px;
            margin-top:6px;
            background: rgba(16,185,129,0.85); /* softer than full bright green */
            opacity: .95;
        }

        .theme-green .member-id {
            color: #145a3a;
            opacity: .9;
        }


        /* ========== COMMON ELEMENTS ========== */
        .brand {
            display:flex;
            gap:12px;
            align-items:center;
        }
        .brand-logo {
            width:36px;
            height:36px;
            border-radius:10px;
            display:grid;
            place-items:center;
            font-weight:900;
            color:#0b1220;
            background: linear-gradient(135deg,#fbbf24,#d97706);
            box-shadow: 0 6px 22px rgba(18,18,18,0.25);
        }
        .badge {
            padding:6px 10px;
            border-radius:999px;
            font-size:11px;
            letter-spacing:.12em;
            background: rgba(243,194,74,0.07);
            color:#f3c24a;
            border: 1px solid rgba(243,194,74,0.25);
            font-weight:700;
        }

        .member-name {
            font-size:26px;
            font-weight:800;
            letter-spacing: .01em;
            margin-top:6px;
            margin-bottom:6px;
            line-height:1;
        }

        .member-meta { display:flex; gap:20px; font-size:12px; align-items:center; color:rgba(255,255,255,0.78); }
        .member-meta.dark { color: rgba(16,24,40,0.9); }

        /* REPLACED AVATAR: placeholder with initials (canvas-safe, no external image) */
        .card-avatar-placeholder {
            width: 76px;
            height: 76px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-weight: 800;
            font-size: 22px;
            color: #145a3a;
            background: linear-gradient(
                135deg,
                #e7f0ea,
                #dfece4
            );
            border: 1px solid rgba(20,90,58,0.08);
            box-shadow: 0 8px 20px rgba(2,6,23,0.05);
        }


        /* keep original image class (unused) but harmless */
        .card-avatar {
            width:76px;
            height:76px;
            border-radius:12px;
            object-fit:cover;
            display:block;
            border: 2px solid rgba(255,255,255,0.06);
            background: rgba(0,0,0,0.06);
            box-shadow: 0 8px 20px rgba(2,6,23,0.5);
        }

        /* ========== BARCODE BACK ========== */
        .barcode-wrap {
            width:100%;
            display:flex;
            justify-content:center;
            margin-top:8px;
        }
        .barcode-box {
            background: #ffffff;
            padding:6px 8px;
            border-radius:10px;
            box-shadow: 0 6px 18px rgba(2,6,23,0.15);
            width: 100%;
            max-width:420px;
            display:flex;
            justify-content:center;
            align-items:center;
        }
        .barcode-img {
            width:100%;
            height:46px;
            object-fit:contain;
            display:block;
        }

        .card-id-text {
            margin-top:8px;
            font-family: 'Share Tech Mono', monospace;
            letter-spacing:.14em;
            font-size:11px;
        }

        .card-footnote {
            font-size:9px;
            opacity:.6;
            margin-top:10px;
            text-align:center;
            line-height:1.2;
        }

        /* offscreen clone wrapper */
        #__wf_print_clone_wrapper {
            position: fixed;
            top: -99999px;
            left: -99999px;
            width: 1px;
            height: 1px;
            overflow: visible;
            pointer-events: none;
            z-index: 9999999;
        }

        /* Responsive small fix for preview only */
        @media (max-width: 900px) {
            .id-card-wrapper { width: 420px; height: 264px; }
        }
    </style>

    <div class="grid lg:grid-cols-2 gap-8 items-start">
        <div class="space-y-8">
            <h2 class="text-xl font-bold text-gray-800">Card Preview</h2>

            {{-- FRONT --}}
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 block">Front Side (Identitas)</span>

                <div id="card-front" class="id-card-wrapper">
                    <div class="card-inner {{ $user->membership_type === 'VIP Lifetime' ? 'theme-vip' : 'theme-green' }}">
                        {{-- decorative diagonals --}}
                        <div class="diagonal" aria-hidden="true"></div>
                        <div class="diagonal--small" aria-hidden="true"></div>

                        {{-- header line --}}
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <div class="brand">
                                <div class="brand-logo">WF</div>
                                <div>
                                    <div style="font-weight:800; font-size:13px; color:inherit;">WFIED</div>
                                    <div style="font-size:11px; opacity:0.8; margin-top:2px;">
                                        {{ $user->membership_type === 'VIP Lifetime' ? 'Exclusive Member' : 'Official Membership' }}
                                    </div>
                                </div>
                            </div>

                            @if($user->membership_type === 'VIP Lifetime')
                                <div class="badge">LIFETIME</div>
                            @else
                                <div style="text-align:right;">
                                    <div style="font-size:11px; color:rgba(15,127,83,0.95); font-weight:700;">
                                        <span class="green-accent">GreenCard</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- main --}}
                        <div style="display:flex; gap:18px; align-items:center; margin-top:12px;">
                            <div style="flex:1;">
                                <div class="member-name {{ $user->membership_type === 'VIP Lifetime' ? '' : 'text-gray-900' }}">
                                    {{ $user->name }}
                                </div>
                                <div style="font-family: 'Share Tech Mono', monospace;" class="{{ $user->membership_type === 'VIP Lifetime' ? '' : 'member-id' }}">
                                    {{ $user->member_id }}
                                </div>

                                <div class="member-meta {{ $user->membership_type === 'VIP Lifetime' ? '' : 'dark' }}" style="margin-top:10px;">
                                    <div>
                                        <div style="font-size:10px; opacity:.7;">Joined</div>
                                        <div style="font-weight:700;">{{ \Carbon\Carbon::parse($user->join_date)->translatedFormat('M Y') }}</div>
                                    </div>

                                    <div>
                                        <div style="font-size:10px; opacity:.7;">Status</div>
                                        <div style="font-weight:700; color: {{ $user->membership_type === 'VIP Lifetime' ? '#34d399' : '#1f6f52' }};">
                                            {{ $user->membership_type === 'VIP Lifetime' ? 'VIP ACTIVE' : 'ACTIVE' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- REPLACED AVATAR: canvas-safe initials placeholder --}}
                            <div style="flex:0 0 auto; text-align:right;">
                                @php
                                    $parts = preg_split('/\s+/', trim($user->name));
                                    $initials = '';
                                    foreach ($parts as $p) {
                                        if ($p !== '') {
                                            $initials .= mb_strtoupper(mb_substr($p, 0, 1));
                                        }
                                    }
                                    // limit to 2-3 chars
                                    $initials = mb_substr($initials, 0, 3);
                                @endphp
                                <div class="card-avatar-placeholder" aria-hidden="true">{{ $initials }}</div>
                            </div>
                        </div>

                        {{-- bottom accent (thin line) --}}
                        @if($user->membership_type === 'VIP Lifetime')
                            <div style="height:6px; width:100%; border-radius:6px; margin-top:6px; background: linear-gradient(90deg,#b37e21,#f3c24a,#b37e21);"></div>
                        @else
                            <div class="bottom-accent"></div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- BACK --}}
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 block">Back Side (Barcode Scanner)</span>

                <div id="card-back" class="id-card-wrapper">
                    <div class="card-inner {{ $user->membership_type === 'VIP Lifetime' ? 'theme-vip' : 'theme-green' }}">
                        <div class="diagonal" aria-hidden="true"></div>

                        <div style="width:100%; text-align:center;">
                            <div style="font-size:12px; font-weight:700; color: {{ $user->membership_type === 'VIP Lifetime' ? '#f3c24a' : '#0f6d49' }};">
                                Scan for Verification
                            </div>
                        </div>

                        <div class="barcode-wrap">
                            <div class="barcode-box">
                                <img id="barcode-img" class="barcode-img" alt="barcode" crossorigin="anonymous">
                            </div>
                        </div>

                        <div class="card-id-text {{ $user->membership_type === 'VIP Lifetime' ? 'vip-accent' : '' }}">
                            {{ $user->member_id }}
                        </div>

                        <div class="card-footnote">
                            This card identifies the holder as a member of WFIED. Non-transferable.
                            @if($user->membership_type === 'VIP Lifetime')
                                <strong style="color:#f3c24a; display:block; margin-top:4px;">PREMIUM LIFETIME SUPPORT ENABLED</strong>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ACTION / DOWNLOAD --}}
        {{-- ACTION / DOWNLOAD --}}
        <div class="lg:sticky lg:top-10 space-y-6">
            {{-- Download Card --}}
            <div class="p-6 rounded-2xl border border-emerald-100 shadow-sm"
                style="background: linear-gradient(180deg,#f0f6f3,#e6efea);">
                <h3 class="font-bold text-gray-800 text-lg mb-1">Download Card</h3>
                <p class="text-sm text-gray-600 mb-5">
                    Unduh kartu digital resolusi tinggi untuk cetak & arsip.
                </p>

                <div class="grid gap-3">
                    <button
                        type="button"
                        onclick="downloadSide('card-front','Front')"
                        class="w-full rounded-xl px-4 py-3 font-semibold flex items-center justify-between
                            border border-emerald-200 text-emerald-800
                            bg-white/70 hover:bg-white transition">
                        <span>Front Side</span>
                        <span class="text-sm opacity-90">PNG</span>
                    </button>

                    <button
                        type="button"
                        onclick="downloadSide('card-back','Back')"
                        class="w-full rounded-xl px-4 py-3 font-semibold flex items-center justify-between
                            border border-emerald-200 text-emerald-800
                            bg-white/70 hover:bg-white transition">
                        <span>Back Side</span>
                        <span class="text-sm opacity-80">PNG</span>
                    </button>
                </div>
            </div>

            {{-- Card Info --}}
            <div class="p-5 rounded-2xl border border-gray-200 text-sm bg-gray-50">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-500">Card Type</span>
                    <span class="font-bold {{ $user->membership_type === 'VIP Lifetime' ? 'vip-accent' : 'green-accent' }}">
                        {{ $user->membership_type }}
                    </span>
                </div>

                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-500">Member ID</span>
                    <span class="font-mono font-bold">{{ $user->member_id }}</span>
                </div>

                <div class="flex justify-between items-center">
                    <span class="text-gray-500">Expired</span>
                    <span class="font-bold">
                        {{ \Carbon\Carbon::parse($user->expiry_date)->translatedFormat('d F Y') }}
                    </span>
                </div>
            </div>
        </div>

    </div>

    {{-- offscreen clone wrapper for stable capture --}}
    <div id="__wf_print_clone_wrapper" aria-hidden="true"></div>

    <script>
        // safe server-to-js values
        const MEMBER_ID = @json($user->member_id);
        const MEMBER_NAME = @json($user->name);

        // Generate preview barcode (visible page) into IMG
        document.addEventListener('DOMContentLoaded', () => {
            try {
                const img = document.getElementById('barcode-img');
                if (img) {
                    // JsBarcode can render into an <img> element directly
                    JsBarcode(img, MEMBER_ID, {
                        format: "CODE128",
                        lineColor: "#000",
                        width: 1.6,   // adapt to visual density
                        height: 48,
                        displayValue: false,
                        background: "#ffffff",
                        margin: 0
                    });
                }
            } catch (e) {
                console.warn('JsBarcode preview error', e);
            }
        });

        // wait for images in a root element
        function waitForImages(root) {
            const imgs = Array.from(root.querySelectorAll('img'));
            return Promise.all(imgs.map(img => {
                return new Promise(resolve => {
                    if (img.complete && img.naturalWidth !== 0) return resolve();
                    img.addEventListener('load', () => resolve(), { once: true });
                    img.addEventListener('error', () => resolve(), { once: true });
                });
            }));
        }

        // Clone -> regenerate barcode -> wait -> capture -> download -> cleanup
        async function downloadSide(elementId, sideName) {
            const original = document.getElementById(elementId);
            if (!original) return alert('Element not found: ' + elementId);

            // deep clone
            const clone = original.cloneNode(true);

            // clear wrapper and insert clone offscreen
            const wrapper = document.getElementById('__wf_print_clone_wrapper');
            wrapper.innerHTML = '';
            wrapper.appendChild(clone);

            // Ensure images won't taint canvas
            clone.querySelectorAll('img').forEach(img => {
                img.setAttribute('crossorigin', 'anonymous');
            });

            // regenerate barcode in the clone (target by id)
            const clonedBarcode = clone.querySelector('#barcode-img');
            if (clonedBarcode) {
                try {
                    JsBarcode(clonedBarcode, MEMBER_ID, {
                        format: "CODE128",
                        lineColor: "#000",
                        width: 1.6,
                        height: 48,
                        displayValue: false,
                        background: "#ffffff",
                        margin: 0
                    });
                } catch (e) {
                    console.warn('JsBarcode error (clone):', e);
                }
            }

            // wait for fonts & clone images to be ready
            try { await document.fonts.ready; } catch(e) { /* ignore if not supported */ }
            await waitForImages(clone);

            // force fixed pixel sizes to avoid responsive scaling differences
            const rect = original.getBoundingClientRect();
            clone.style.width = rect.width + 'px';
            clone.style.height = rect.height + 'px';
            clone.style.transform = 'none';
            clone.style.position = 'relative';
            clone.style.left = '0';
            clone.style.top = '0';

            // capture
            try {
                const canvas = await html2canvas(clone, {
                    scale: 3,
                    useCORS: true,
                    backgroundColor: null,
                    logging: false
                });

                const a = document.createElement('a');
                a.download = `WFIED-${sideName}-${MEMBER_ID}.png`;
                a.href = canvas.toDataURL('image/png');
                a.click();
            } catch (err) {
                console.error('html2canvas error:', err);
                alert('Gagal membuat gambar. Buka console untuk detail.');
            } finally {
                // cleanup
                wrapper.innerHTML = '';
            }
        }
    </script>
</x-filament-panels::page>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ str_pad($payment->id, 5, '0', STR_PAD_LEFT) }}</title>
    
    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        /* Agar saat Print Background Warna tetap muncul */
        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .page-break { page-break-inside: avoid; }
        }
    </style>
</head>

<body class="bg-gray-100 p-8 font-sans relative">

{{-- ================= WATERMARK STATUS ================= --}}
@if($payment->status === 'rejected')
<div class="absolute inset-0 flex items-center justify-center pointer-events-none z-50 fixed">
    <div class="border-8 border-red-500 text-red-500 text-9xl font-black opacity-20 rotate-[-30deg] px-6 py-4 uppercase">
        REJECTED
    </div>
</div>
@endif

@if($payment->status === 'approved' || $payment->status === 'active')
<div class="absolute inset-0 flex items-center justify-center pointer-events-none z-50 fixed">
    <div class="border-8 border-green-500 text-green-500 text-9xl font-black opacity-20 rotate-[-30deg] px-6 py-4 uppercase">
        PAID
    </div>
</div>
@endif

@if(in_array($payment->status, ['pending_upload','waiting_verification']))
<div class="absolute top-6 right-6 no-print">
    <span class="bg-gray-200 text-gray-600 text-xs font-bold px-3 py-1 rounded uppercase tracking-wide">
        UNPAID DRAFT
    </span>
</div>
@endif

{{-- ================= INVOICE CONTAINER ================= --}}
<div class="max-w-3xl mx-auto bg-white p-12 shadow-xl rounded-xl print:shadow-none print:p-0 print:max-w-none">

    {{-- HEADER --}}
    <div class="flex justify-between items-start mb-12">
        <div>
            <h1 class="text-4xl font-extrabold text-gray-800 uppercase tracking-wide">Invoice</h1>
            <p class="text-gray-500 mt-2">
                #INV-{{ date('Y') }}-{{ str_pad($payment->id, 5, '0', STR_PAD_LEFT) }}
            </p>
        </div>
        <div class="text-right">
            {{-- UPDATED: Mengambil dari GeneralSetting Baru --}}
            <h2 class="text-xl font-bold text-gray-700">{{ $settings->organization_name ?? 'WFIED' }}</h2>
            <p class="text-gray-500 text-sm mt-1 whitespace-pre-line max-w-xs ml-auto">
                {{ $settings->organization_address }}
            </p>
            {{-- UPDATED: Nama kolom berubah jadi tax_number --}}
            @if($settings->tax_number)
                <p class="text-gray-500 text-sm mt-1 font-mono">Tax ID/NPWP: {{ $settings->tax_number }}</p>
            @endif
        </div>
    </div>

    {{-- BILLING INFO --}}
    <div class="flex justify-between mb-12">
        <div class="w-1/2">
            <h3 class="text-xs font-bold text-gray-400 uppercase mb-2">Billed To</h3>
            <p class="text-lg font-bold text-gray-800">{{ $payment->user->name }}</p>
            
            <div class="text-sm text-gray-600 mt-1 space-y-1">
                <p>{{ $payment->user->email }}</p>
                @if($payment->user->phone) <p>{{ $payment->user->phone }}</p> @endif
                @if($payment->user->organization) <p class="font-medium">{{ $payment->user->organization }}</p> @endif
                @if($payment->user->member_id) <p class="text-blue-600 font-bold">ID: {{ $payment->user->member_id }}</p> @endif
            </div>
        </div>

        <div class="w-1/2 text-right">
            <h3 class="text-xs font-bold text-gray-400 uppercase mb-2">Details</h3>

            <div class="flex justify-between border-b py-2 text-sm">
                <span class="text-gray-600">Invoice Date</span>
                <span class="font-bold">{{ $payment->created_at->format('d M Y') }}</span>
            </div>

            <div class="flex justify-between border-b py-2 text-sm">
                <span class="text-gray-600">Due Date</span>
                {{-- Asumsi jatuh tempo 7 hari --}}
                <span class="font-bold">{{ $payment->created_at->addDays(7)->format('d M Y') }}</span>
            </div>

            {{-- STATUS BADGE --}}
            <div class="flex justify-between py-2 items-center">
                <span class="text-gray-600 text-sm">Status</span>

                @php
                    $statusMap = [
                        'pending_upload' => ['UNPAID', 'bg-gray-100 text-gray-700'],
                        'waiting_verification' => ['UNDER REVIEW', 'bg-yellow-100 text-yellow-800'],
                        'approved' => ['PAID', 'bg-green-100 text-green-800'],
                        'active' => ['PAID', 'bg-green-100 text-green-800'],
                        'rejected' => ['REJECTED', 'bg-red-100 text-red-700'],
                    ];
                    [$label, $color] = $statusMap[$payment->status] ?? [$payment->status, 'bg-gray-100 text-gray-600'];
                @endphp

                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ $color }}">
                    {{ $label }}
                </span>
            </div>
        </div>
    </div>

    {{-- ITEMS TABLE --}}
    <table class="w-full mb-12">
        <thead>
            <tr class="bg-gray-50 border-b-2 border-gray-200">
                <th class="text-left py-3 px-4 uppercase text-sm font-bold text-gray-600">Description</th>
                <th class="text-right py-3 px-4 uppercase text-sm font-bold text-gray-600">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="py-4 px-4 border-b">
                    <p class="font-bold text-gray-800">
                        {{ $payment->type == 'renewal' ? 'Membership Renewal' : 'New Membership Registration' }}
                    </p>
                    <p class="text-sm text-gray-500">
                        Plan: {{ strtoupper($payment->user->membership_type ?? 'Standard') }}
                    </p>
                    @if($payment->admin_note)
                        <p class="text-xs text-gray-400 italic mt-1">Note: {{ $payment->admin_note }}</p>
                    @endif
                </td>
                <td class="py-4 px-4 border-b text-right font-mono text-gray-800 font-bold">
                    {{ $settings->currency ?? 'IDR' }} {{ number_format($payment->amount) }}
                </td>
            </tr>
        </tbody>
    </table>

    {{-- TOTAL --}}
    <div class="flex justify-end mb-12 page-break">
        <div class="w-1/2">
            <div class="flex justify-between py-3 border-b border-gray-300">
                <span class="font-bold text-gray-600">Total Due</span>
                <span class="font-extrabold text-2xl text-blue-600">
                    {{ $settings->currency ?? 'IDR' }} {{ number_format($payment->amount) }}
                </span>
            </div>
        </div>
    </div>

    {{-- PAYMENT INSTRUCTIONS (UPDATED: MULTI BANK) --}}
    @if($payment->status !== 'approved' && $payment->status !== 'active')
    <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 mb-8 page-break">
        <h3 class="text-sm font-bold uppercase text-gray-700 mb-4 border-b pb-2">Payment Instructions</h3>
        
        @if($banks->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($banks as $bank)
                    <div class="bg-white p-4 rounded border border-gray-200">
                        <div class="flex items-center gap-2 mb-1">
                            @if($bank->logo)
                                <img src="{{ asset('storage/'.$bank->logo) }}" class="h-6 object-contain">
                            @else
                                <span class="text-xs font-bold bg-gray-200 px-1 rounded">BANK</span>
                            @endif
                            <p class="font-bold text-gray-800">{{ $bank->bank_name }}</p>
                        </div>
                        
                        <div class="text-sm space-y-1 mt-2">
                            <div class="flex justify-between">
                                <span class="text-gray-500 text-xs">Account No:</span>
                                <span class="font-mono font-bold">{{ $bank->account_number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 text-xs">Beneficiary:</span>
                                <span class="font-medium">{{ $bank->account_owner }}</span>
                            </div>
                            @if($bank->swift_code)
                            <div class="flex justify-between">
                                <span class="text-gray-500 text-xs">SWIFT Code:</span>
                                <span class="font-mono text-xs">{{ $bank->swift_code }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500 italic">No bank account available. Please contact admin.</p>
        @endif
        
        <p class="text-xs text-gray-400 mt-4 text-center">
            Please upload your proof of payment in the member dashboard after transfer.
        </p>
    </div>
    @endif

    {{-- FOOTER --}}
    <div class="text-center text-gray-500 text-sm border-t pt-8 page-break">
        <p class="font-bold">{{ $settings->organization_name ?? 'WFIED' }}</p>
        @if($settings->support_email) <p>Email: {{ $settings->support_email }}</p> @endif
        <p class="mt-2 text-xs text-gray-400">System generated invoice.</p>

        {{-- PRINT BUTTON (Hidden saat diprint) --}}
        <button onclick="window.print()"
            class="no-print mt-6 px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-bold shadow-lg transition transform hover:-translate-y-0.5 flex items-center gap-2 mx-auto">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Print / Save as PDF
        </button>
    </div>

</div>

<script>
    // Opsional: Otomatis print saat halaman dibuka (jika diinginkan)
    // window.onload = function() { window.print(); }
</script>

</body>
</html>
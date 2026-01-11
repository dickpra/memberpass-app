<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ str_pad($payment->id, 5, '0', STR_PAD_LEFT) }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none; }
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>

<body class="bg-gray-100 p-8 font-sans relative">

{{-- ================= WATERMARK ================= --}}
@if($payment->status === 'rejected')
<div class="absolute inset-0 flex items-center justify-center pointer-events-none z-50">
    <div class="border-8 border-red-500 text-red-500 text-9xl font-black opacity-20 rotate-[-30deg] px-6 py-4 uppercase">
        REJECTED
    </div>
</div>
@endif

@if($payment->status === 'approved' || $payment->status === 'active')
<div class="absolute inset-0 flex items-center justify-center pointer-events-none z-50">
    <div class="border-8 border-green-500 text-green-500 text-9xl font-black opacity-20 rotate-[-30deg] px-6 py-4 uppercase">
        PAID
    </div>
</div>
@endif

@if(in_array($payment->status, ['pending_upload','waiting_verification']))
<div class="absolute top-6 right-6">
    <span class="bg-gray-200 text-gray-600 text-xs font-bold px-3 py-1 rounded uppercase tracking-wide">
        UNPAID DRAFT
    </span>
</div>
@endif

{{-- ================= INVOICE ================= --}}
<div class="max-w-3xl mx-auto bg-white p-12 shadow-xl rounded-xl print:shadow-none print:p-0">

    {{-- HEADER --}}
    <div class="flex justify-between items-start mb-12">
        <div>
            <h1 class="text-4xl font-extrabold text-gray-800 uppercase tracking-wide">Invoice</h1>
            <p class="text-gray-500 mt-2">
                #INV-{{ date('Y') }}-{{ str_pad($payment->id, 5, '0', STR_PAD_LEFT) }}
            </p>
        </div>
        <div class="text-right">
            <h2 class="text-xl font-bold text-gray-700">{{ $settings->organization_name }}</h2>
            <p class="text-gray-500 text-sm mt-1 whitespace-pre-line">
                {{ $settings->organization_address }}
            </p>
            @if($settings->vat_number)
                <p class="text-gray-500 text-sm mt-1">VAT / NPWP: {{ $settings->vat_number }}</p>
            @endif
        </div>
    </div>

    {{-- BILLING --}}
    <div class="flex justify-between mb-12">
        <div class="w-1/2">
            <h3 class="text-xs font-bold text-gray-400 uppercase mb-2">Billed To</h3>
            <p class="text-lg font-bold text-gray-800">{{ $payment->user->name }}</p>
            <p class="text-gray-600">{{ $payment->user->email }}</p>
            <p class="text-gray-600">{{ $payment->user->phone }}</p>
            <p class="text-gray-600">{{ $payment->user->country }}</p>
            @if($payment->user->organization)
                <p class="text-gray-600 font-medium">{{ $payment->user->organization }}</p>
            @endif
        </div>

        <div class="w-1/2 text-right">
            <h3 class="text-xs font-bold text-gray-400 uppercase mb-2">Details</h3>

            <div class="flex justify-between border-b py-2">
                <span class="text-gray-600">Invoice Date</span>
                <span class="font-bold">{{ $payment->created_at->format('d M Y') }}</span>
            </div>

            <div class="flex justify-between border-b py-2">
                <span class="text-gray-600">Due Date</span>
                <span class="font-bold">{{ $payment->created_at->addDays(7)->format('d M Y') }}</span>
            </div>

            {{-- STATUS BADGE --}}
            <div class="flex justify-between py-2">
                <span class="text-gray-600">Status</span>

                @php
                    $statusMap = [
                        'pending_upload' => ['UNPAID', 'bg-gray-100 text-gray-700'],
                        'waiting_verification' => ['UNDER REVIEW', 'bg-yellow-100 text-yellow-800'],
                        'approved' => ['PAID', 'bg-green-100 text-green-800'],
                        'active' => ['ACTIVE', 'bg-green-200 text-green-900'],
                        'rejected' => ['REJECTED', 'bg-red-100 text-red-700'],
                    ];
                    [$label, $color] = $statusMap[$payment->status] ?? ['UNKNOWN', 'bg-gray-100 text-gray-600'];
                @endphp

                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ $color }}">
                    {{ $label }}
                </span>
            </div>
        </div>
    </div>

    {{-- ITEM --}}
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
                        Membership Plan: {{ strtoupper($payment->user->membership_type) }}
                    </p>
                    <p class="text-sm text-gray-500">1 Year Subscription</p>
                </td>
                <td class="py-4 px-4 border-b text-right font-mono">
                    {{ $settings->currency }} {{ number_format($payment->amount, 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    {{-- TOTAL --}}
    <div class="flex justify-end mb-12">
        <div class="w-1/2">
            <div class="flex justify-between py-3 border-b">
                <span class="font-bold text-gray-600">Total Due</span>
                <span class="font-extrabold text-2xl text-blue-600">
                    {{ $settings->currency }} {{ number_format($payment->amount, 2) }}
                </span>
            </div>
        </div>
    </div>

    {{-- PAYMENT INFO --}}
    <div class="bg-gray-50 p-6 rounded-lg border mb-8">
        <h3 class="text-sm font-bold uppercase text-gray-700 mb-4">Payment Instructions</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-400 text-xs">Bank Name</span>
                <p class="font-bold">{{ $settings->bank_name }}</p>
            </div>
            <div>
                <span class="text-gray-400 text-xs">Account Number</span>
                <p class="font-bold">{{ $settings->bank_account_number }}</p>
            </div>
            <div>
                <span class="text-gray-400 text-xs">Beneficiary</span>
                <p class="font-bold">{{ $settings->bank_account_owner }}</p>
            </div>
            @if($settings->bank_swift_code)
            <div>
                <span class="text-gray-400 text-xs">SWIFT / BIC</span>
                <p class="font-bold">{{ $settings->bank_swift_code }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- FOOTER --}}
    <div class="text-center text-gray-500 text-sm border-t pt-8">
        <p>Thank you for your business.</p>
        <button onclick="window.print()"
            class="no-print mt-6 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-bold shadow">
            Print / Save as PDF
        </button>
    </div>

</div>
</body>
</html>

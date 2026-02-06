<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Donation Receipt</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.6; }
        .container { max-width: 700px; margin: 0 auto; border: 1px solid #ddd; padding: 40px; }
        
        /* HEADER */
        .header { border-bottom: 2px solid #22c55e; padding-bottom: 20px; margin-bottom: 30px; display: table; width: 100%; }
        .logo-area { display: table-cell; vertical-align: middle; width: 60%; }
        .receipt-title { display: table-cell; vertical-align: middle; text-align: right; width: 40%; }
        .org-name { font-size: 24px; font-weight: bold; color: #111; margin: 0; }
        .org-address { font-size: 12px; color: #666; margin-top: 5px; }
        
        h1 { font-size: 28px; color: #22c55e; margin: 0; text-transform: uppercase; letter-spacing: 2px; }
        .receipt-no { font-size: 12px; color: #888; margin-top: 5px; }

        /* CONTENT */
        .thank-you { text-align: center; margin: 30px 0; font-size: 16px; font-style: italic; color: #555; }
        
        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .details-table td { padding: 12px; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; width: 35%; color: #555; }
        .value { font-weight: bold; color: #000; }
        
        /* AMOUNT BOX */
        .amount-box { background: #f0fdf4; border: 1px solid #22c55e; padding: 15px; text-align: center; margin-bottom: 30px; border-radius: 5px; }
        .amount-label { font-size: 12px; text-transform: uppercase; color: #15803d; letter-spacing: 1px; }
        .amount-value { font-size: 32px; font-weight: bold; color: #166534; margin: 5px 0 0 0; }

        /* FOOTER */
        .footer { margin-top: 50px; text-align: center; font-size: 10px; color: #aaa; border-top: 1px solid #eee; padding-top: 20px; }
        .signature { margin-top: 40px; text-align: right; }
        .sign-line { border-top: 1px solid #333; width: 200px; display: inline-block; margin-top: 60px; }
        
        /* BADGE */
        .paid-stamp {
            position: absolute; right: 50px; top: 180px;
            border: 3px solid #ef4444; color: #ef4444;
            font-size: 24px; font-weight: bold; padding: 5px 15px;
            transform: rotate(-15deg); opacity: 0.8; letter-spacing: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <div class="header">
            <div class="logo-area">
                {{-- Ganti dengan path logo WFIEd Anda --}}
                <p class="org-name">{{ $settings->organization_name ?? 'WFIEd Global' }}</p>
                <div class="org-address">
                    Building Inclusive Education Worldwide<br>
                    Website: membership.wfied.com | Email: admin@wfied.com
                </div>
            </div>
            <div class="receipt-title">
                <h1>Receipt</h1>
                <div class="receipt-no">NO: DON-{{ $donation->created_at->format('Y') }}-{{ str_pad($donation->id, 5, '0', STR_PAD_LEFT) }}</div>
                <div class="receipt-no">DATE: {{ $donation->created_at->format('d M Y') }}</div>
            </div>
        </div>

        <div class="paid-stamp">VERIFIED</div>

        <div class="thank-you">
            "Thank you for your generous contribution. Your support helps us build a more inclusive world."
        </div>

        <div class="amount-box">
            <div class="amount-label">Donation Amount Received</div>
            <div class="amount-value">
                {{ $donation->currency }} {{ number_format($donation->amount, 0, ',', '.') }}
            </div>
        </div>

        <table class="details-table">
            <tr>
                <td class="label">Donor Name</td>
                <td class="value">{{ $donation->user->name }}</td>
            </tr>
            <tr>
                <td class="label">Member ID</td>
                <td class="value">{{ $donation->user->member_id ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Donation Program</td>
                <td class="value">{{ $donation->program->title }}</td>
            </tr>
            <tr>
                <td class="label">Payment Method</td>
                <td class="value">
                    {{ $donation->paymentMethod->provider_name ?? 'Manual Transfer' }}
                    ({{ $donation->paymentMethod->currency_code ?? '' }})
                </td>
            </tr>
            <tr>
                <td class="label">Payment Status</td>
                <td class="value" style="color: #22c55e;">SUCCESS / VERIFIED</td>
            </tr>
        </table>

        <div class="signature">
            <div class="sign-line"></div>
            <div style="font-size: 12px; font-weight: bold;">Finance Department</div>
            <div style="font-size: 10px;">WFIEd Management</div>
        </div>

        <div class="footer">
            <p>This is a computer-generated receipt and requires no physical signature.<br>
            WFIEd is a registered non-profit organization.</p>
        </div>

    </div>
</body>
</html>
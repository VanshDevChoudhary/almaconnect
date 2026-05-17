<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: "DejaVu Sans", sans-serif; }
        body { color: #1f2937; font-size: 13px; }
        .brand { font-size: 22px; font-weight: bold; color: #4f46e5; }
        .title { font-size: 18px; margin: 24px 0 4px; }
        .muted { color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        td { padding: 8px 4px; border-bottom: 1px solid #e5e7eb; }
        td.label { color: #6b7280; width: 40%; }
        .footer { margin-top: 40px; font-size: 11px; color: #9ca3af; }
        .amount { font-size: 20px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="brand">AlmaConnect</div>
    <div class="title">Donation Receipt</div>
    <div class="muted">This is a system-generated receipt (not a tax certificate).</div>

    <table>
        <tr>
            <td class="label">Receipt / Donation ID</td>
            <td>#{{ $donation->id }}</td>
        </tr>
        <tr>
            <td class="label">Date</td>
            <td>{{ $donation->created_at->format('F j, Y') }}</td>
        </tr>
        <tr>
            <td class="label">Donor</td>
            <td>{{ $donation->is_anonymous ? 'Anonymous Donor' : $donation->user->name }}</td>
        </tr>
        <tr>
            <td class="label">Campaign</td>
            <td>{{ $donation->campaign->title ?? 'General Fund' }}</td>
        </tr>
        <tr>
            <td class="label">Payment ID</td>
            <td>{{ $donation->razorpay_payment_id ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Amount</td>
            <td class="amount">&#8377; {{ number_format((int) $donation->amount) }}</td>
        </tr>
    </table>

    <div class="footer">
        AlmaConnect — Alumni Cell. Thank you for your generous contribution.
        For queries, contact {{ config('almaconnect.contact_email') }}.
    </div>
</body>
</html>

{{-- PDF receipt (English, dompdf). Inline CSS only — dompdf does not load the site bundle. --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Receipt {{ $receipt->receipt_no }}</title>
<style>
    @page { margin: 22px 28px; }
    body { font-family: DejaVu Sans, sans-serif; color: #1f2733; font-size: 11px; }
    .watermark { position: fixed; top: 32%; left: 0; right: 0; text-align: center; font-size: 110px; color: #0a1f3d; opacity: 0.05; font-weight: bold; }
    .head { text-align: center; border-bottom: 2px solid #c9a227; padding-bottom: 8px; margin-bottom: 10px; }
    .head img { height: 46px; }
    .mark { margin: 0 auto; width: 44px; height: 44px; border-radius: 22px; background: #0e2a52; }
    .mark td { width: 44px; height: 44px; text-align: center; vertical-align: middle; color: #dab84f; font-weight: bold; font-size: 16px; padding: 0; }
    .org { font-size: 17px; font-weight: bold; color: #0a1f3d; margin-top: 4px; }
    .addr { color: #5b6472; font-size: 10px; }
    .title { font-size: 12px; letter-spacing: 2px; color: #a9791f; font-weight: bold; margin-top: 4px; }
    table.meta { width: 100%; margin-bottom: 8px; }
    table.meta td { padding: 2px 0; }
    table.rows { width: 100%; border-collapse: collapse; }
    table.rows td { padding: 6px 4px; border-bottom: 1px solid #e3e0d8; vertical-align: top; }
    table.rows td.k { color: #5b6472; width: 34%; }
    .amount { font-size: 18px; font-weight: bold; color: #0a1f3d; }
    .words { font-style: italic; color: #5b6472; }
    .foot { margin-top: 14px; width: 100%; }
    .sign { text-align: right; padding-top: 26px; }
    .note { margin-top: 10px; color: #5b6472; font-size: 9px; text-align: center; }
</style>
</head>
<body>
<div class="watermark">KS</div>

<div class="head">
    @if ($logo)<img src="{{ $logo }}" alt="">@else<table class="mark"><tr><td>KS</td></tr></table>@endif
    <div class="org">{{ setting('org_name_en') }}</div>
    <div class="addr">{{ setting('address_en') }}@if (setting('phone_primary')) · {{ setting('phone_primary') }}@endif @if (setting('email')) · {{ setting('email') }}@endif</div>
    <div class="title">PAYMENT RECEIPT</div>
</div>

<table class="meta">
    <tr><td><strong>Receipt No:</strong> {{ $receipt->receipt_no }}</td><td style="text-align:right"><strong>Date:</strong> {{ ($payment->paid_at ?? $receipt->issued_at)->format('d M Y') }}</td></tr>
</table>

<table class="rows">
    <tr><td class="k">Received from</td><td><strong>{{ $payment->displayName() }}</strong>@if ($payment->member?->membership_no) ({{ $payment->member->membership_no }})@endif</td></tr>
    @if ($payment->payer_mobile)<tr><td class="k">Mobile</td><td>{{ $payment->payer_mobile }}</td></tr>@endif
    <tr><td class="k">Purpose</td><td>{{ $purpose }}</td></tr>
    <tr><td class="k">Amount</td><td><span class="amount">Rs. {{ number_format((float) $payment->amount, 2) }}</span><br><span class="words">{{ $amountWords }}</span></td></tr>
    <tr><td class="k">Payment mode</td><td>{{ $payment->mode->label() }}@if ($payment->offline_method) — {{ $payment->offline_method->label() }}@endif @if ($payment->provider !== 'offline') ({{ ucfirst($payment->provider) }})@endif</td></tr>
    @if ($payment->reference())<tr><td class="k">Transaction / Reference</td><td>{{ $payment->reference() }}</td></tr>@endif
</table>

<table class="foot"><tr>
    <td style="width:60%;color:#5b6472;font-size:10px;">Thank you for supporting the {{ setting('org_short_name_en') }}.</td>
    <td class="sign">For {{ setting('org_name_en') }}<br><br><strong>Authorised Signatory</strong></td>
</tr></table>

<div class="note">This is a computer-generated receipt and does not require a physical signature.</div>
</body>
</html>

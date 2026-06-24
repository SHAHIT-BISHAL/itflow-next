<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 13px; color: #1e293b; background: #fff; }
        .container { padding: 40px; max-width: 800px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; }
        .logo { font-size: 22px; font-weight: 700; color: #6366f1; }
        .invoice-meta { text-align: right; }
        .invoice-number { font-size: 20px; font-weight: 700; color: #1e293b; }
        .badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-yellow { background: #fef9c3; color: #854d0e; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .badge-gray { background: #f1f5f9; color: #475569; }
        .addresses { display: flex; justify-content: space-between; margin-bottom: 32px; }
        .address-block { flex: 1; }
        .address-block + .address-block { margin-left: 40px; }
        .label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; font-weight: 600; margin-bottom: 4px; }
        .company-name { font-weight: 700; font-size: 15px; color: #1e293b; }
        .text-muted { color: #64748b; }
        .dates { display: flex; gap: 40px; margin-bottom: 32px; padding: 16px; background: #f8fafc; border-radius: 8px; }
        .date-item { }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        thead tr { background: #f1f5f9; }
        th { padding: 10px 12px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; }
        th:last-child, td:last-child { text-align: right; }
        td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; }
        .totals { width: 280px; margin-left: auto; }
        .totals tr td { border: none; padding: 5px 0; }
        .totals .total-row td { font-size: 15px; font-weight: 700; border-top: 2px solid #1e293b; padding-top: 8px; margin-top: 4px; }
        .notes { margin-top: 32px; padding: 16px; background: #f8fafc; border-radius: 8px; }
        .payments { margin-top: 24px; }
        .footer { margin-top: 48px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 16px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div>
            <div class="logo">{{ config('app.name', 'ITFlow-Next') }}</div>
            <div style="margin-top: 4px; color: #64748b; font-size: 12px;">
                {{ $invoice->company->email ?? '' }}
            </div>
        </div>
        <div class="invoice-meta">
            <div class="invoice-number">{{ $invoice->invoice_number }}</div>
            <div style="margin-top: 6px;">
                @php
                    $colorClass = match($invoice->status) {
                        'paid'    => 'badge-green',
                        'partial' => 'badge-blue',
                        'sent'    => 'badge-yellow',
                        'overdue' => 'badge-red',
                        default   => 'badge-gray',
                    };
                @endphp
                <span class="badge {{ $colorClass }}">{{ ucfirst($invoice->status) }}</span>
            </div>
        </div>
    </div>

    <div class="addresses">
        <div class="address-block">
            <div class="label">From</div>
            <div class="company-name">{{ $invoice->company->name ?? config('app.name') }}</div>
        </div>
        <div class="address-block">
            <div class="label">Bill To</div>
            <div class="company-name">{{ $invoice->client->name }}</div>
            @if($invoice->contact)
                <div class="text-muted">{{ $invoice->contact->name }}</div>
                @if($invoice->contact->email)
                    <div class="text-muted">{{ $invoice->contact->email }}</div>
                @endif
            @endif
        </div>
    </div>

    <div class="dates">
        <div class="date-item">
            <div class="label">Issue Date</div>
            <div>{{ $invoice->issue_date->format('d M Y') }}</div>
        </div>
        <div class="date-item">
            <div class="label">Due Date</div>
            <div>{{ $invoice->due_date->format('d M Y') }}</div>
        </div>
        <div class="date-item">
            <div class="label">Currency</div>
            <div>{{ $invoice->currency }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:50%">Description</th>
                <th style="width:12%; text-align:right">Qty</th>
                <th style="width:16%; text-align:right">Unit Price</th>
                <th style="width:10%; text-align:right">Tax %</th>
                <th style="width:12%; text-align:right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td style="text-align:right">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                <td style="text-align:right">${{ number_format($item->unit_price, 2) }}</td>
                <td style="text-align:right">{{ $item->tax_rate > 0 ? $item->tax_rate . '%' : '—' }}</td>
                <td style="text-align:right">${{ number_format($item->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="text-muted">Subtotal</td>
            <td>${{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td class="text-muted">Tax</td>
            <td>${{ number_format($invoice->tax_amount, 2) }}</td>
        </tr>
        @if($invoice->amount_paid > 0)
        <tr>
            <td class="text-muted">Paid</td>
            <td style="color:#22c55e">-${{ number_format($invoice->amount_paid, 2) }}</td>
        </tr>
        @endif
        <tr class="total-row">
            <td>{{ $invoice->status === 'paid' ? 'Total Paid' : 'Amount Due' }}</td>
            <td>${{ number_format($invoice->amount_due, 2) }}</td>
        </tr>
    </table>

    @if($invoice->payments->count())
    <div class="payments">
        <div class="label" style="margin-bottom: 8px;">Payment History</div>
        @foreach($invoice->payments as $payment)
        <div style="display:flex; justify-content:space-between; padding: 4px 0; border-bottom: 1px solid #f1f5f9;">
            <span class="text-muted">{{ $payment->paid_at->format('d M Y') }} — {{ ucfirst(str_replace('_', ' ', $payment->method ?? 'payment')) }}</span>
            <span style="font-weight:600; color:#22c55e">${{ number_format($payment->amount, 2) }}</span>
        </div>
        @endforeach
    </div>
    @endif

    @if($invoice->notes)
    <div class="notes">
        <div class="label" style="margin-bottom: 4px;">Notes</div>
        <div>{{ $invoice->notes }}</div>
    </div>
    @endif

    @if($invoice->terms)
    <div class="notes" style="margin-top: 12px;">
        <div class="label" style="margin-bottom: 4px;">Terms</div>
        <div>{{ $invoice->terms }}</div>
    </div>
    @endif

    <div class="footer">
        Thank you for your business. · Generated {{ now()->format('d M Y') }}
    </div>
</div>
</body>
</html>

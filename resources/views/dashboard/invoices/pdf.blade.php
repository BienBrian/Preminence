<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoiceNumber }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 13px; color: #333; padding: 40px; }
        h1 { color: #4e73df; font-size: 32px; margin-bottom: 4px; }
        .invoice-meta { color: #666; font-size: 12px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .status-paid { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f8f9fc; padding: 10px 12px; text-align: left; border-bottom: 2px solid #e3e6f0; }
        td { padding: 10px 12px; border-bottom: 1px solid #e3e6f0; }
        .text-end { text-align: right; }
        .total-row { background: #f8f9fc; font-weight: bold; }
        .payment-note { background: #d4edda; border-left: 4px solid #28a745; padding: 12px 16px; margin-top: 20px; }
        @media print {
            body { padding: 20px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align:right; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 8px 20px; background: #4e73df; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Print / Save as PDF
        </button>
        <button onclick="window.history.back()" style="padding: 8px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 8px;">
            Back
        </button>
    </div>

    <div class="header">
        <div>
            <h1>INVOICE</h1>
            <div class="invoice-meta"># {{ $invoiceNumber }}</div>
            <div class="invoice-meta" style="margin-top: 4px;">
                Date: {{ $firstItem->created_at ? \Carbon\Carbon::parse($firstItem->created_at)->format('d M Y') : '—' }}
            </div>
        </div>
        <div style="text-align: right;">
            <div class="status-badge {{ $firstItem->status === 'paid' ? 'status-paid' : 'status-pending' }}">
                {{ strtoupper($firstItem->status) }}
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Period</th>
                <th class="text-end">Amount (KES)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>
                        <strong>{{ ucfirst($item->module_key) }} Module</strong><br>
                        <small style="color:#666;">{{ $item->type ?? 'Subscription' }}</small>
                    </td>
                    <td>
                        @if($item->period_start && $item->period_end)
                            {{ \Carbon\Carbon::parse($item->period_start)->format('d M Y') }}
                            — {{ \Carbon\Carbon::parse($item->period_end)->format('d M Y') }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-end">{{ number_format($item->total_amount, 2) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2" class="text-end">TOTAL</td>
                <td class="text-end">KES {{ number_format($total, 2) }}</td>
            </tr>
        </tbody>
    </table>

    @if($firstItem->status === 'paid' && $firstItem->paid_at)
        <div class="payment-note">
            ✓ Payment received on {{ \Carbon\Carbon::parse($firstItem->paid_at)->format('d M Y, h:i A') }}
            @if($firstItem->payment_method)
                via {{ ucfirst($firstItem->payment_method) }}
            @endif
        </div>
    @endif
</body>
</html>

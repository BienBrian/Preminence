<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MPESA Transactions Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #333;
            padding: 15px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 10px;
            color: #666;
        }
        .summary-box {
            background: #f5f5f5;
            padding: 8px 12px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-size: 10px;
        }
        .summary-box p {
            margin: 2px 0;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            background: #333;
            color: #fff;
            padding: 6px 10px;
            margin-bottom: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 4px 6px;
            text-align: left;
            font-size: 9px;
        }
        th {
            background: #f0f0f0;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-row {
            background: #e8f4e8;
            font-weight: bold;
        }
        .grand-total {
            background: #333;
            color: #fff;
            font-weight: bold;
            font-size: 11px;
        }
        .badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-matched {
            background: #28a745;
            color: white;
        }
        .badge-unmatched {
            background: #dc3545;
            color: white;
        }
        .badge-hash {
            background: #17a2b8;
            color: white;
        }
        .footer {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
        .group-header {
            background: #e9ecef;
            font-weight: bold;
            font-size: 10px;
        }
        @media print {
            body {
                padding: 8px;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>
            @if($filteredCategory)
                {{ $filteredCategory->name }} - Fund Source Report
            @else
                MPESA Transactions Report
            @endif
        </h1>
        <p>{{ config('app.name') }}</p>
        <p>Generated on: {{ now()->format('d M Y, H:i') }}</p>
        @if($filteredCategory)
            <p style="color: {{ $filteredCategory->color }}; font-weight: bold;">
                <i class="fas fa-filter"></i> Showing only transactions for: {{ $filteredCategory->name }}
            </p>
        @endif
    </div>

    <div class="summary-box">
        <p><strong>Date Range:</strong> {{ $dateFrom }} to {{ $dateTo }}</p>
        @if($filteredCategory)
            <p style="background: {{ $filteredCategory->color }}20; padding: 5px; border-left: 4px solid {{ $filteredCategory->color }};">
                <strong><i class="fas fa-filter"></i> Filtered by Fund Source:</strong> {{ $filteredCategory->name }}
            </p>
        @endif
        <p><strong>Total Amount:</strong> KES {{ number_format($grandTotal, 2) }}</p>
        <p><strong>Total Transactions:</strong> {{ $groupedData->flatten(1)->count() }}</p>
        <p><strong>Grouping:</strong> 
            @if($request->boolean('group_by_category'))
                Grouped by Fund Source (Category)
            @elseif($request->boolean('group_references'))
                Grouped by Reference Type
            @else
                Ungrouped
            @endif
        </p>
    </div>

    {{-- Fund Source Summary Section --}}
    @if(!empty($fundSourceSummary))
    <div class="section">
        <div class="section-title" style="background: #1e40af;">Fund Source Summary</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th>Fund Source (Category)</th>
                    <th class="text-right">Transactions</th>
                    <th class="text-right">Total Amount</th>
                    <th class="text-right">% of Total</th>
                </tr>
            </thead>
            <tbody>
                @php $rank = 1; @endphp
                @foreach($fundSourceSummary as $categoryName => $summary)
                <tr style="border-left: 4px solid {{ $summary['color'] }}">
                    <td class="text-center">{{ $rank++ }}</td>
                    <td>
                        <strong>{{ $categoryName }}</strong>
                        @if($summary['category_id'])
                            <small class="text-muted">(ID: {{ $summary['category_id'] }})</small>
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($summary['count']) }}</td>
                    <td class="text-right">KES {{ number_format($summary['total'], 2) }}</td>
                    <td class="text-right">{{ $grandTotal > 0 ? number_format(($summary['total'] / $grandTotal) * 100, 1) : 0 }}%</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="grand-total">
                    <td colspan="2"><strong>GRAND TOTAL</strong></td>
                    <td class="text-right">{{ number_format($groupedData->flatten(1)->count()) }}</td>
                    <td class="text-right"><strong>KES {{ number_format($grandTotal, 2) }}</strong></td>
                    <td class="text-right">100%</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    @foreach($groupedData as $groupName => $transactions)
    <div class="section">
        <div class="section-title" @if($request->boolean('group_by_category') && isset($fundSourceSummary[$groupName])) style="background: {{ $fundSourceSummary[$groupName]['color'] }};" @endif>
            @if($request->boolean('group_by_category'))
                <i class="fas fa-folder"></i> Fund Source: {{ $groupName }} 
            @else
                {{ $groupName }}
            @endif
            <span style="float: right;">
                Count: {{ $transactions->count() }} | 
                Total: KES {{ number_format($transactions->sum('TransAmount'), 2) }}
            </span>
        </div>
        
        <table>
            <thead>
                <tr>
                    @if(in_array('trans_id', $selectedColumns))
                    <th style="width: 10%;">Trans ID</th>
                    @endif
                    @if(in_array('name', $selectedColumns))
                    <th style="width: 15%;">Name</th>
                    @endif
                    @if(in_array('amount', $selectedColumns))
                    <th style="width: 8%;" class="text-right">Amount</th>
                    @endif
                    @if(in_array('account', $selectedColumns))
                    <th style="width: 10%;">Account</th>
                    @endif
                    @if(in_array('category', $selectedColumns))
                    <th style="width: 10%;">Category</th>
                    @endif
                    @if(in_array('grouped_ref', $selectedColumns))
                    <th style="width: 10%;">Grouped As</th>
                    @endif
                    @if(in_array('msisdn', $selectedColumns))
                    <th style="width: 10%;">MSISDN</th>
                    @endif
                    @if(in_array('match_status', $selectedColumns))
                    <th style="width: 10%;">Match Status</th>
                    @endif
                    @if(in_array('matched_user', $selectedColumns))
                    <th style="width: 12%;">Matched User</th>
                    @endif
                    @if(in_array('date', $selectedColumns))
                    <th style="width: 12%;">Date</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $tx)
                <tr>
                    @if(in_array('trans_id', $selectedColumns))
                    <td>{{ $tx->TransID }}</td>
                    @endif
                    @if(in_array('name', $selectedColumns))
                    <td>{{ $tx->FirstName }} {{ $tx->MiddleName }} {{ $tx->LastName }}</td>
                    @endif
                    @if(in_array('amount', $selectedColumns))
                    <td class="text-right">{{ number_format($tx->TransAmount, 2) }}</td>
                    @endif
                    @if(in_array('account', $selectedColumns))
                    <td>{{ $tx->BillRefNumber }}</td>
                    @endif
                    @if(in_array('category', $selectedColumns))
                    <td>{{ $tx->CategoryName ?? '-' }}</td>
                    @endif
                    @if(in_array('grouped_ref', $selectedColumns))
                    <td>{{ $tx->GroupedRef !== $tx->BillRefNumber ? $tx->GroupedRef : '-' }}</td>
                    @endif
                    @if(in_array('msisdn', $selectedColumns))
                    <td>{{ strlen($tx->MSISDN) <= 15 && is_numeric($tx->MSISDN) ? $tx->MSISDN : substr($tx->MSISDN, 0, 16) . '...' }}</td>
                    @endif
                    @if(in_array('match_status', $selectedColumns))
                    <td>
                        @php
                            $statusClass = 'badge-unmatched';
                            if ($tx->match_status === 'Matched') $statusClass = 'badge-matched';
                            elseif ($tx->match_status === 'Hash Matched') $statusClass = 'badge-hash';
                        @endphp
                        <span class="badge {{ $statusClass }}">{{ $tx->match_status }}</span>
                    </td>
                    @endif
                    @if(in_array('matched_user', $selectedColumns))
                    <td>{{ $tx->matched_user ?? '-' }}</td>
                    @endif
                    @if(in_array('date', $selectedColumns))
                    <td>{{ $tx->created_at ? \Carbon\Carbon::parse($tx->created_at)->format('d M Y, H:i') : '-' }}</td>
                    @endif
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="totals-row">
                    <td colspan="{{ count(array_intersect(['trans_id', 'name'], $selectedColumns)) }}" style="text-align: right;">
                        <strong>Subtotal for {{ $groupName }}</strong>
                    </td>
                    @if(in_array('amount', $selectedColumns))
                    <td class="text-right"><strong>{{ number_format($transactions->sum('TransAmount'), 2) }}</strong></td>
                    @endif
                    <td colspan="{{ count(array_diff($selectedColumns, ['trans_id', 'name', 'amount'])) }}"></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endforeach

    <div class="section">
        <table>
            <tfoot>
                <tr class="grand-total">
                    <td colspan="{{ count(array_intersect(['trans_id', 'name'], $selectedColumns)) }}" style="text-align: right;">
                        <strong>GRAND TOTAL</strong>
                    </td>
                    @if(in_array('amount', $selectedColumns))
                    <td class="text-right"><strong>KES {{ number_format($grandTotal, 2) }}</strong></td>
                    @endif
                    <td colspan="{{ count(array_diff($selectedColumns, ['trans_id', 'name', 'amount'])) }}">
                        {{ $groupedData->flatten(1)->count() }} transactions
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="footer no-print">
        <p>This report was generated automatically from the MPESA transaction logs.</p>
        <p>
            <button onclick="window.print()" style="padding: 8px 20px; margin: 10px; cursor: pointer;">
                <i class="fas fa-print"></i> Print Report
            </button>
            <button onclick="window.close()" style="padding: 8px 20px; margin: 10px; cursor: pointer;">
                <i class="fas fa-times"></i> Close
            </button>
        </p>
    </div>

    <script>
        // Auto-print on load
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>

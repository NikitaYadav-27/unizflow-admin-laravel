<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounting Ledger Report - {{ date('d M Y') }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #1e293b;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .title {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }
        .subtitle {
            color: #64748b;
            font-size: 12px;
            margin-top: 4px;
        }
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }
        .kpi-card {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
            background: #f8fafc;
        }
        .kpi-label {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 4px;
        }
        .kpi-val {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
        }
        .credit { color: #16a34a; }
        .debit { color: #dc2626; }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 600;
            text-align: left;
            padding: 8px 6px;
            border: 1px solid #cbd5e1;
        }
        td {
            padding: 6px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        tr:nth-child(even) {
            background-color: #fafafa;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: 600;
            border-radius: 4px;
        }
        .badge-credit {
            background: #dcfce7;
            color: #15803d;
        }
        .badge-debit {
            background: #fee2e2;
            color: #b91c1c;
        }
        .no-print {
            margin-bottom: 16px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" style="padding: 8px 16px; background: #0f172a; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">
            Print / Save to PDF
        </button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #e2e8f0; color: #334155; border: none; border-radius: 4px; cursor: pointer; margin-left: 8px;">
            Close
        </button>
    </div>

    <div class="header">
        <div>
            <h1 class="title">Provider Commission Ledger</h1>
            <div class="subtitle">
                Period: {{ $startDate ?? 'Beginning' }} to {{ $endDate ?? 'Present' }} | Generated on {{ date('d M Y, h:i A') }}
            </div>
        </div>
        <div style="text-align: right;">
            <div style="font-weight: 700; font-size: 14px; color: #0f172a;">UNIZ ADMIN</div>
            <div class="subtitle">Auto-Accounting Report</div>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-label">Opening Amount</div>
            <div class="kpi-val">₹ {{ number_format($openingAmount, 2) }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Closing Amount</div>
            <div class="kpi-val">₹ {{ number_format($closingAmount, 2) }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Total Credit</div>
            <div class="kpi-val credit">₹ {{ number_format($totalCredit, 2) }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Total Debit</div>
            <div class="kpi-val debit">₹ {{ number_format($totalDebit, 2) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 35px;">ID</th>
                <th style="width: 80px;">Panel</th>
                <th style="width: 85px;">Txn Type</th>
                <th style="width: 60px;">Type</th>
                <th style="width: 90px;">Amount</th>
                <th style="width: 45px;">Mode</th>
                <th>Notes</th>
                <th style="width: 80px;">Date</th>
                <th style="width: 95px;">Opening</th>
                <th style="width: 95px;">Closing</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $row)
                <tr>
                    <td>{{ $row->id }}</td>
                    <td>{{ $row->panel }}</td>
                    <td>{{ $row->txn_type }}</td>
                    <td>
                        <span class="badge {{ $row->type === 'Credit' ? 'badge-credit' : 'badge-debit' }}">
                            {{ $row->type }}
                        </span>
                    </td>
                    <td><strong>₹ {{ number_format($row->amount, 2) }}</strong></td>
                    <td>{{ $row->mode ?? 'NA' }}</td>
                    <td>{{ $row->notes }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->date)->format('Y-m-d') }}</td>
                    <td>₹ {{ number_format($row->opening, 2) }}</td>
                    <td>₹ {{ number_format($row->closing, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center; padding: 20px; color: #94a3b8;">
                        No ledger records found for the selected period.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        window.addEventListener('load', function () {
            // Auto open print dialog when loaded
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>


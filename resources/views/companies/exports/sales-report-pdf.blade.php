<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Sales Report - {{ $company->name }} - {{ $year }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #1e293b;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #6366f1;
        }

        .header h1 {
            color: #6366f1;
            margin: 0 0 10px 0;
            font-size: 24px;
        }

        .header p {
            color: #64748b;
            margin: 0;
        }

        .summary-box {
            background: #f1f5f9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .summary-label {
            color: #64748b;
        }

        .summary-value {
            font-weight: bold;
            color: #1e293b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            background: #6366f1;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
        }

        td {
            padding: 10px 8px;
            border-bottom: 1px solid #e2e8f0;
        }

        tr:nth-child(even) {
            background: #f8fafc;
        }

        .amount {
            text-align: right;
            font-weight: 600;
        }

        .total-row {
            background: #1e293b !important;
            color: white;
            font-weight: bold;
        }

        .total-row td {
            border: none;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            color: #64748b;
            font-size: 10px;
        }

        .achieved {
            color: #10b981;
        }

        .not-achieved {
            color: #ef4444;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ $company->name }}</h1>
        <p>Sales Report for {{ $year }}</p>
    </div>

    <div class="summary-box">
        <table style="margin: 0; border: none;">
            <tr>
                <td style="border: none; padding: 5px 10px;">
                    <span class="summary-label">Total Orders:</span>
                    <strong>{{ array_sum(array_column($monthlySummary, 'orders')) }}</strong>
                </td>
                <td style="border: none; padding: 5px 10px;">
                    <span class="summary-label">Total Revenue:</span>
                    <strong>{{ $company->currency_symbol }}{{ number_format(array_sum(array_column($monthlySummary, 'revenue')), 2) }}</strong>
                </td>
                <td style="border: none; padding: 5px 10px;">
                    <span class="summary-label">Total Target:</span>
                    <strong>{{ $company->currency_symbol }}{{ number_format(array_sum(array_column($monthlySummary, 'target')), 2) }}</strong>
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>Month</th>
                <th>Orders</th>
                <th style="text-align: right;">Revenue</th>
                <th style="text-align: right;">Target</th>
                <th style="text-align: right;">Achieved</th>
            </tr>
        </thead>
        <tbody>
            @foreach($monthlySummary as $monthData)
                @php
                    $achieved = $monthData['target'] > 0
                        ? round(($monthData['revenue'] / $monthData['target']) * 100, 1)
                        : 0;
                @endphp
                <tr>
                    <td>{{ $monthData['month_name'] }}</td>
                    <td>{{ $monthData['orders'] }}</td>
                    <td class="amount">{{ $company->currency_symbol }}{{ number_format($monthData['revenue'], 2) }}</td>
                    <td class="amount">
                        {{ $monthData['target'] > 0 ? $company->currency_symbol . number_format($monthData['target'], 2) : '-' }}
                    </td>
                    <td
                        class="amount {{ $achieved >= 100 ? 'achieved' : ($monthData['target'] > 0 ? 'not-achieved' : '') }}">
                        {{ $monthData['target'] > 0 ? $achieved . '%' : '-' }}
                    </td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td><strong>TOTAL</strong></td>
                <td><strong>{{ array_sum(array_column($monthlySummary, 'orders')) }}</strong></td>
                <td class="amount">
                    <strong>{{ $company->currency_symbol }}{{ number_format(array_sum(array_column($monthlySummary, 'revenue')), 2) }}</strong>
                </td>
                <td class="amount">
                    <strong>{{ $company->currency_symbol }}{{ number_format(array_sum(array_column($monthlySummary, 'target')), 2) }}</strong>
                </td>
                <td class="amount">
                    @php
                        $totalRevenue = array_sum(array_column($monthlySummary, 'revenue'));
                        $totalTarget = array_sum(array_column($monthlySummary, 'target'));
                        $overallAchieved = $totalTarget > 0 ? round(($totalRevenue / $totalTarget) * 100, 1) : 0;
                    @endphp
                    <strong>{{ $totalTarget > 0 ? $overallAchieved . '%' : '-' }}</strong>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on {{ now()->format('F d, Y \a\t h:i A') }}</p>
        <p>CRM-Minimal-Carbon - Company Sales Report</p>
    </div>
</body>

</html>
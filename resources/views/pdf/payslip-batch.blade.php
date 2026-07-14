<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payroll {{ $run->periodLabel() }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #4f46e5; color: white; padding: 6px; text-align: left; }
        td { padding: 5px; border-bottom: 1px solid #e5e7eb; }
        .header { border-bottom: 2px solid #4f46e5; padding-bottom: 10px; margin-bottom: 10px; }
        .total { font-weight: bold; background: #f3f4f6; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $tenant->name }} — Payroll Statement</h2>
        <p>Period: {{ $run->periodLabel() }} | Status: {{ ucfirst($run->status) }}</p>
    </div>
    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Basic</th>
                <th>HRA</th>
                <th>Allowances</th>
                <th>Gross</th>
                <th>PF</th>
                <th>ESI</th>
                <th>TDS</th>
                <th>Deductions</th>
                <th>Net Pay</th>
            </tr>
        </thead>
        <tbody>
            @foreach($run->entries as $entry)
            <tr>
                <td>{{ $entry->user->name }}</td>
                <td>{{ number_format($entry->basic_salary, 0) }}</td>
                <td>{{ number_format($entry->hra, 0) }}</td>
                <td>{{ number_format($entry->allowances, 0) }}</td>
                <td>{{ number_format($entry->gross_salary, 0) }}</td>
                <td>{{ number_format($entry->pf_deduction, 0) }}</td>
                <td>{{ number_format($entry->esi_deduction, 0) }}</td>
                <td>{{ number_format($entry->tds_deduction, 0) }}</td>
                <td>{{ number_format($entry->total_deductions, 0) }}</td>
                <td><strong>{{ number_format($entry->net_salary, 0) }}</strong></td>
            </tr>
            @endforeach
            <tr class="total">
                <td colspan="4">TOTAL</td>
                <td>{{ number_format($run->total_gross, 0) }}</td>
                <td colspan="3"></td>
                <td>{{ number_format($run->total_deductions, 0) }}</td>
                <td><strong>{{ number_format($run->total_net, 0) }}</strong></td>
            </tr>
        </tbody>
    </table>
</body>
</html>

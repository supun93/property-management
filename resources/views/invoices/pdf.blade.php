<!DOCTYPE html>
<html>

<head>
    <title>Invoice #{{ $invoice->id }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <h2>Invoice #{{ $invoice->id }}</h2>
    <p><strong>Name:</strong> {{ $invoice->name }}</p>
    <p><strong>Status:</strong> {{ $invoice->status == 1 ? 'Paid' : 'Unpaid' }}</p>

    @php
    $firstLine = $invoice->lines->first();
    @endphp

    @if($firstLine)
    @php
    $schedule = $firstLine->unitPaymentSchedule;
    $contract = optional($schedule)->contract;
    $unit = optional($contract)->unit;
    $tenant = optional($contract)->tenant;
    $month = optional($schedule?->payment_date)
    ? \Carbon\Carbon::parse($schedule->payment_date)->format('F Y')
    : '-';
    @endphp

    <p><strong>Unit:</strong> {{ $unit->unit_name ?? '-' }}</p>
    <p><strong>Tenant:</strong> {{ $tenant->name ?? '-' }}</p>
    <p><strong>Month:</strong> {{ $month }}</p>

    @endif

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Amount (LKR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->lines as $line)
            <tr>
                <td>{{ $line->description }}</td>
                <td style="text-align:right;">{{ number_format($line->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3 style="text-align:right; margin-top: 20px;">
        Total: Rs. {{ number_format($invoice->lines->sum('amount'), 2) }}
    </h3>
</body>

</html>
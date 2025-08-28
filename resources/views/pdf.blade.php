<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tickets Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .unit-discount { font-size: 10px; color: #555; display: block; }
        h1, h2 { margin-bottom: 10px; }
    </style>
</head>
<body>

<h1>Tickets Report</h1>
<p>
    Document for {{ $from->format('F Y') }} to {{ $to->format('F Y') }}
</p>

<h2>Summary</h2>
<table>
    <tr>
        <td>Monthly Package Amount × Months</td>
        <td>{{ number_format($monthlyPackageAmount, 2) }} × {{ $months }}</td>
        <td>= {{ number_format($monthlyPackageAmount * $months, 2) }}</td>
    </tr>
    <tr>
        <td>Extra Unit Amount × Extra Count</td>
        <td>{{ number_format($extraUnitAmount, 2) }} × {{ $extraCount }}</td>
        <td>= {{ number_format($extraUnitAmount * $extraCount, 2) }}</td>
    </tr>
    <tr>
        <th>Total</th>
        <th colspan="2">{{ number_format($total, 2) }}</th>
    </tr>
</table>

<h2>Regular Tickets</h2>
<table>
    <thead>
        <tr>
            <th>Subject</th>
            <th>Created At</th>
            <th>Ticket Weight</th>
            <th>Holiday Multiplier</th>
            <th>Timeslot Multiplier</th>
            <th>Unit Value</th>
        </tr>
    </thead>
    <tbody>
        @foreach($regularTickets as $ticket)
        <tr>
            <td>{{ $ticket->subject }}</td>
            <td>{{ $ticket->created_at->format('d M Y H:i:s') }}</td>
            <td>{{ $ticket->ticket_weight }}</td>
            <td>{{ $ticket->holiday_multiplier }}</td>
            <td>{{ $ticket->timeslot_multiplier }}</td>
            <td>
                {{ $ticket->unit_value }}
                @if($ticket->unit_discount > 0)
                    <span class="unit-discount">Unit Discount: {{ $ticket->unit_discount }}</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<h2>Excess Tickets</h2>
<table>
    <thead>
        <tr>
            <th>Subject</th>
            <th>Unit Value</th>
            <th>Created At</th>
        </tr>
    </thead>
    <tbody>
        @foreach($excessTickets as $ticket)
        <tr>
            <td>{{ $ticket->subject }}</td>
            <td>
                {{ $ticket->unit_value }}
                @if($ticket->unit_discount > 0)
                    <span class="unit-discount">Unit Discount: {{ $ticket->unit_discount }}</span>
                @endif
            </td>
            <td>{{ $ticket->created_at->format('d M Y H:i:s') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>

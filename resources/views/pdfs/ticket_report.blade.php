<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tickets Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .page-break { page-break-after: always; }
        .header-section, .summary-section {
            margin-bottom: 25px;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 5px;
        }
        .header-section h1 { margin-top: 0; }
        .summary-section table { width: 400px; }
        .summary-section td { border: none; padding: 5px; }
        .summary-section .label { font-weight: bold; }
        .summary-section .value { text-align: right; }
        .summary-section .grand-total { font-weight: bold; border-top: 1px solid #ccc; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; vertical-align: top; }
        th { background-color: #f4f4f4; }
        .unit-discount { font-size: 10px; color: #555; display: block; margin-top: 4px;}
        .text-right { text-align: right; }
    </style>
</head>
<body>

<div class="header-section">
    <h1>Monthly Support Report</h1>
    <p>
        <strong>Client:</strong> {{ $client->name }}<br>
        <strong>Period:</strong> {{ $fromDate->format('F Y') }} to {{ $toDate->format('F Y') }}
    </p>
</div>

<div class="summary-section">
    <h2>Billing Summary</h2>
    <table>
        <tr>
            <td class="label">Monthly Package Amount:</td>
            <td class="value">${{ number_format($totalPackageAmount, 2) }}</td>
            <td>({{ $numberOfMonths }} months @ ${{ number_format($client->package->base_price, 2) }}/month)</td>
        </tr>
        <tr>
            <td class="label">Excess Unit Amount:</td>
            <td class="value">${{ number_format($totalExcessAmount, 2) }}</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td class="label grand-total">Grand Total:</td>
            <td class="value grand-total">${{ number_format($grandTotal, 2) }}</td>
            <td>&nbsp;</td>
        </tr>
    </table>
</div>


<h2>Regular Tickets (Within Quota)</h2>
<table>
    <thead>
        <tr>
            <th>Subject</th>
            <th>Created At</th>
            <th>Weight</th>
            <th>Holiday Mult.</th>
            <th>Timeslot Mult.</th>
            <th>Unit Value</th>
        </tr>
    </thead>
    <tbody>
        @forelse($regularTickets as $ticket)
        <tr>
            <td>{{ $ticket->subject }}</td>
            <td>{{ $ticket->created_at->format('d M Y H:i') }}</td>
            <td>{{ $ticket->ticket_weight }}</td>
            <td>{{ $ticket->holiday_multiplier }}</td>
            <td>{{ $ticket->timeslot_multiplier }}</td>
            <td>
                {{ $ticket->unit_value }}
                @if($ticket->unit_discount > 0)
                    <span class="unit-discount">(Discount: {{ $ticket->unit_discount }})</span>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6">No regular tickets found for this period.</td>
        </tr>
        @endforelse
    </tbody>
</table>

<h2>Excess Tickets (Beyond Quota)</h2>
<table>
    <thead>
        <tr>
            <th>Subject</th>
            <th>Created At</th>
            <th>Unit Value</th>
        </tr>
    </thead>
    <tbody>
        @forelse($excessTickets as $ticket)
        <tr>
            <td>{{ $ticket->subject }}</td>
            <td>{{ $ticket->created_at->format('d M Y H:i') }}</td>
            <td>
                ${{ number_format($ticket->unit_value, 2) }}
                @if($ticket->unit_discount > 0)
                    <span class="unit-discount">(Discount: {{ $ticket->unit_discount }})</span>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="3">No excess tickets found for this period.</td>
        </tr>
        @endforelse
    </tbody>
</table>

</body>
</html>
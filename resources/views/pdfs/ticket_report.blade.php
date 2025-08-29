<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Tickets Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        .page-break {
            page-break-after: always;
        }

        .header-section,
        .summary-section {
            margin-bottom: 0px;
            padding: 0px;
        }

        .header-section h1 {
            margin-top: 0;
        }

        .summary-section table {
            width: 400px;
        }

        .summary-section td {
            border: none;
            padding: 5px;
        }

        .summary-section .label {
            font-weight: bold;
        }

        .summary-section .value {
            text-align: right;
        }

        .summary-section .grand-total {
            font-weight: bold;
            border-top: 1px solid #ccc;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #f4f4f4;
        }

        .unit-discount {
            font-size: 10px;
            color: #555;
            display: block;
            margin-top: 4px;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>

<div class="header-section" style="border: 1px solid #eee; padding: 10px 10px 1px 10px; border-radius: 5px;">
    <table style="width: 100%; border-collapse: collapse; border: none;">
        <tr>
            <td style="vertical-align: top; border: none;">
                <h1 style="margin:0;">Monthly Support Report</h1>
                <p style="margin: 0 0 0 0; /* top right bottom left */">
                    <strong>Client:</strong> {{ $client->name }}<br>
                    <strong>Period:</strong> {{ $fromDate->format('F Y') }}
                </p>
            </td>
            <td style="text-align: right; vertical-align: top; border: none;">
                <img src="{{ public_path('storage/dsi_logo.PNG') }}" alt="DSI Logo" style="height: 60px; max-width: 150px;">
            </td>
        </tr>
    </table>
</div>




    <h2>Regular Tickets (Within Quota)</h2>
    <table>
        <thead>
            <tr>
                <th>Subject</th>
                <th>Created At</th>
                <th>Resolved At</th>
                <th>Weight</th>
                <th>Holiday Mult.</th>
                <th>Timeslot Mult.</th>
                <th>Unit Count</th>
            </tr>
        </thead>
        <tbody>
            @forelse($regularTickets as $ticket)
                <tr>
                    <td>{{ $ticket->subject }}</td>
                    <td>
                        {{ $ticket->created_at->format('d M Y') }}<br>
                        {{ $ticket->created_at->format('h:i A') }}
                    </td>
                    <td>
                        @if($ticket->status === \App\Enums\TicketStatus::CLOSED)
                            {{ $ticket->updated_at->format('d M Y') }}<br>
                            {{ $ticket->updated_at->format('h:i A') }}
                        @else
                            -
                        @endif

                    </td>
                    <td>{{ $ticket->ticket_weight }}</td>
                    <td>{{ $ticket->holiday_multiplier }}</td>
                    <td>{{ $ticket->timeslot_multiplier }}</td>
                    <td>
                        {{ number_format($ticket->unit_value) }}
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
                <th>Resolved At</th>

                <th>Excess Unit Price</th>
            </tr>
        </thead>
        <tbody>
            @forelse($excessTickets as $ticket)
                <tr>
                    <td>{{ $ticket->subject }}</td>
                    <td>
                        {{ $ticket->created_at->format('d M Y') }}<br>
                        {{ $ticket->created_at->format('h:i A') }}
                    </td>
                    <td>
                        @if($ticket->status === \App\Enums\TicketStatus::CLOSED)
                            {{ $ticket->updated_at->format('d M Y') }}<br>
                            {{ $ticket->updated_at->format('h:i A') }}
                        @else
                            -
                        @endif

                    </td>
                    <td>
                        {{ number_format($ticket->unit_value * $client->package->excess_unit_price) }}

                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">No excess tickets found for this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>


    <div class="summary-section">
        <h2>Billing Summary</h2>
        <table>
            <tr>
                <td class="label">Monthly Package Amount:</td>
                <td class="value">{{ number_format($totalPackageAmount, 2) }}</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="label">Excess Unit Amount:</td>
                <td class="value">{{ number_format($totalExcessAmount * $client->package->excess_unit_price, 2) }}</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="label grand-total">Grand Total:</td>
                <td class="value grand-total">
                    {{ number_format($totalPackageAmount + $totalExcessAmount * $client->package->excess_unit_price, 2) }}
                </td>
                <td>&nbsp;</td>
            </tr>
        </table>
    </div>

</body>

</html>
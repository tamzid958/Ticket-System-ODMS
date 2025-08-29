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

        .summary-section {
            margin-bottom: 0px;
            padding: 0px;
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

        /* --- ADDED FOR DOMPDF TABLE PAGE BREAKS --- */
        thead {
            display: table-header-group;
            /* repeat headers on each page */
        }

        tfoot {
            display: table-footer-group;
            /* repeat footers if any */
        }

        tr {
            page-break-inside: avoid;
            /* do not split rows */
        }
    </style>
</head>

<body>

    <div>
        <table style="width: 100%; border-collapse: collapse; border: none;">
            <tr>
                <td style="vertical-align: top; border: none; width: 100%; padding: 0;">
                    <h1 style="margin:0;">Monthly Support Report</h1>
                    <p style="margin: 0;">
                        <strong>Client:</strong> {{ $client->name }}<br>
                        <strong>Period:</strong> {{ $fromDate->format('F Y') }}
                    </p>
                </td>

                <!-- Right side: logo -->
                <td style="text-align: right; vertical-align: top; border: none; padding: 0;">
                    <img src="{{ public_path('storage/dsi_logo.PNG') }}" alt="DSI Logo"
                        style="height: 60px; max-width: 150px;">
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
                    <td colspan="7">No regular tickets found for this period.</td>
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
            @php $totalExcessPrice = 0; @endphp

            @forelse($excessTickets as $ticket)
                @php $totalExcessPrice += $ticket->unit_value * $client->package->excess_unit_price; @endphp
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
                    <td style="text-align: right;">
                        {{ number_format($ticket->unit_value * $client->package->excess_unit_price) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">No excess tickets found for this period.</td>
                </tr>
            @endforelse

            <!-- Total Row -->
            @if($excessTickets->count() > 0)
                <tr>
                    <td colspan="3" style="text-align: right; font-weight: bold;">Total:</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($totalExcessPrice) }}</td>
                </tr>
            @endif
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
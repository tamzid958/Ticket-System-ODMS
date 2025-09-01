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

        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-footer-group;
        }

        tr {
            page-break-inside: avoid;
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
                        <strong>Period:</strong> {{ $fromDate->format('F Y') }}<br/>
                        <strong>Package:</strong> {{ $package->name }}
                    </p>
                </td>

                <td style="text-align: right; vertical-align: top; border: none; padding: 0;">
                    <img src="{{ public_path('storage/dsi_logo.PNG') }}" alt="Dynamic Solution Innovators"
                        style="height: 60px; max-width: 150px;">
                </td>
            </tr>
        </table>
    </div>

    <h2>Regular Tickets Summary</h2>
    <table>
        <thead>
            <tr>
                <th>Ticket Count</th>
                <th>Multiplier(timeslot × weekend × weight )</th>
                <th>Effective Units (Ticket Count × Multiplier)</th>
            </tr>
        </thead>
        <tbody>
            @php $totalEffectiveUnits = 0; @endphp
            @php $totalUnitDiscount = 0; @endphp
            @forelse($countMultipliers as $pair)
                @php
                    $ticketCount = $pair[0];
                    $multiplier = $pair[1];
                    $unitDiscount = $pair[2] ?? 0;
                    $totalUnitDiscount += $unitDiscount;
                    $effectiveUnits = $ticketCount * $multiplier;
                    $totalEffectiveUnits += $effectiveUnits
                @endphp
                <tr>
                    <td>{{ $ticketCount }}</td>
                    <td>{{ $multiplier }}</td>
                    <td>{{ $effectiveUnits }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">No regular tickets found for this period.</td>
                </tr>
            @endforelse
                <tr>
                    <td colspan="2" style="text-align: right; font-weight: bold;">Total Effective Units:</td>
                    <td style="font-weight: bold;">{{ $totalEffectiveUnits }}</td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: right; font-weight: bold;">Total unit discount:</td>
                    <td style="font-weight: bold;">{{ $totalUnitDiscount }}</td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: right; font-weight: bold;">Total Effective Units after unit discount:</td>
                    <td style="font-weight: bold;">{{ $totalEffectiveUnits - $totalUnitDiscount }}</td>
                </tr>

        </tbody>
    </table>


    <h2>Excess Tickets (Beyond Quota)</h2>
    <table>
        <thead>
            <tr>
                <th>Total Excess Tickets</th>
                <th>Per Unit Price</th>
            </tr>
        </thead>
        <tbody>
            @php
                $excessTicketCount = $excessTickets->count();
                $totalExcessPrice = $excessTicketCount * $excessUnitPrice;
            @endphp

            <tr>
                <td>{{ $excessTicketCount }}</td>
                <td>{{ number_format($excessUnitPrice, 2) }}</td>
            </tr>
            <tr>
                <td colspan="1" style="text-align: right; font-weight: bold;">Total Excess Price:</td>
                <td style="font-weight: bold;">{{ number_format($totalExcessPrice, 2) }}</td>
            </tr>
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
                <td class="label">Monthly Package Discount:</td>
                <td class="value">- {{ number_format($packageDiscount, 2) }}</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="label">Excess Unit Amount:</td>
                <td class="value">{{ number_format($totalExcessPrice, 2) }}</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="label grand-total">Grand Total After Discount:</td>
                <td class="value grand-total">
                    {{ number_format($totalPackageAmount + $totalExcessPrice - $packageDiscount, 2) }}
                </td>
                <td>&nbsp;</td>
            </tr>
        </table>
    </div>

</body>

</html>

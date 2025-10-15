<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Monthly Summary Generated</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h2>Monthly Summary Generated</h2>

    <p>Hello,</p>

    <p>A new monthly summary has been generated for the client <strong>{{ $clientName }}</strong> for <strong>{{ \Carbon\Carbon::create()->month($month)->format('F') }}, {{ $year }}</strong>.</p>

    <p>Here are the details:</p>
    <ul>
        <li><strong>Client Name:</strong> {{ $clientName }}</li>
        <li><strong>Month:</strong> {{ \Carbon\Carbon::create()->month($month)->format('F') }}</li>
        <li><strong>Year:</strong> {{ $year }}</li>
        <li><strong>Total Cost:</strong> ${{ number_format($totalCost, 2) }}</li>
    </ul>

    <p>You can view the full summary report by clicking the link below:</p>

    <p><a href="{{ $summaryUrl }}" style="color: #1a73e8;">View Monthly Summary</a></p>

    <p>Regards,<br>{{ config('app.name') }} Team</p>
</body>
</html>

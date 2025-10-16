<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Client Monthly Summaries Generated</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h2 style="color: #1a73e8;">Client Monthly Summaries Generated</h2>

    <p>Hello,</p>

    <p>The monthly summaries for <strong>{{ $month->format('F Y') }}</strong> have been successfully generated.</p>

    <p>All client reports for this period have been bundled into a single ZIP file, which is attached to this email.</p>

    <p>If you need to review the details of individual clients, please extract the attached ZIP and open the corresponding PDF reports.</p>

    <p>Regards,<br>{{ config('app.name') }} Team</p>

    <hr>
    <small style="color: #666;">
        This is an automated message. Please do not reply directly to this email.
    </small>
</body>
</html>

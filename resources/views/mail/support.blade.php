<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Ticket Notification</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f7; color: #333; margin: 0; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; padding: 30px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="text-align: center; font-size: 26px; font-weight: bold; color: #0d6efd; padding-bottom: 25px; font-family: 'Segoe UI', sans-serif;">
                            New Ticket Notification
                        </td>
                    </tr>

                    <tr>
                        <td style="padding-bottom: 20px; font-size: 16px;">
                            <p>Dear Support Engineers,</p>
                            <p>A new ticket has been created by client: <strong>{{ $clientName }}</strong></p>
                            <p>Created by: <strong>{{ $creatorName }}</strong></p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding-bottom: 20px; font-size: 15px;">
                            <p style="margin: 5px 0; font-weight: 600;">Ticket Title:</p>
                            <p style="margin: 5px 0;">{{ $ticketTitle }}</p>

                            <p style="margin: 10px 0;"><strong>Created At:</strong> {{ $createdAt }}</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="text-align: center; padding: 20px 0;">
                            <a href="{{ url('/panel/tickets/' . $ticket->id) }}" target="_blank" style="background-color: #0d6efd; color: #ffffff; text-decoration: none; padding: 12px 25px; border-radius: 5px; display: inline-block; font-weight: 600;">
                                View Ticket
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding-top: 15px; font-size: 14px; color: #555;">
                            Thank you.<br>
                            <span style="font-weight: 600;">Dynamic Solutions Innovators</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

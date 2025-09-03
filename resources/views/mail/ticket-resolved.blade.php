<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Resolved Notification</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            color: #334155;
            margin: 0;
            padding: 20px;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        .email-header {
            background: linear-gradient(to right, #f0fff4, #dcfce7);
            color: #166534;
            padding: 28px 30px;
            text-align: center;
            border-bottom: 1px solid #dcfce7;
        }
        .email-body {
            padding: 30px;
        }
        .ticket-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 20px 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        }
        .ticket-table th {
            background-color: #f8fafc;
            padding: 16px;
            text-align: left;
            font-weight: 600;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }
        .ticket-table td {
            padding: 16px;
            border-bottom: 1px solid #f1f5f9;
        }
        .ticket-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .ticket-table tr:nth-child(odd) {
            background-color: #ffffff;
        }
        .ticket-table tr:last-child td {
            border-bottom: none;
        }
        .text-button {
            display: inline-block;
            color: #0d9488;
            text-decoration: none;
            padding: 14px 0;
            font-weight: 600;
            margin: 20px 0;
            transition: color 0.2s;
            border-bottom: 2px solid #0d9488;
        }
        .text-button:hover {
            color: #0f766e;
            border-bottom-color: #0f766e;
        }
        .note-box {
            background-color: #f0fdf4;
            border-left: 4px solid #22c55e;
            padding: 18px;
            border-radius: 6px;
            margin: 25px 0;
        }
        .description-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 16px;
            border-radius: 6px;
            margin: 15px 0;
            font-size: 15px;
            line-height: 1.5;
        }
        .footer {
            padding: 25px 30px;
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 14px;
        }
        .company-name {
            color: #4f46e5;
            font-weight: 600;
        }
        @media (max-width: 650px) {
            .email-container {
                width: 100%;
                border-radius: 0;
            }
            .email-body {
                padding: 20px;
            }
            .ticket-table {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1 style="margin: 0; font-size: 28px; font-weight: 600;">ODMS Support Ticket Resolved</h1>
        </div>

        <div class="email-body">
            <h3><p>Dear {{ $creatorName }},</p></h3>
            <p style="font-size: 15px;">We're delighted to inform you that your support request has been successfully resolved by our team. We truly appreciate your patience and understanding throughout this process.</p>

            <p style="font-size: 15px;">It's our pleasure to assist valued clients like you, and we hope our solution meets your expectations. Please don't hesitate to reach out if you need any further assistance.</p>

            <table class="ticket-table">
                <tr>
                    <th colspan="2">Ticket Information</th>
                </tr>
                <tr>
                    <td style="width: 35%; font-weight: 600; color: #475569;">Ticket Title:</td>
                    <td>{{ $ticketTitle }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 600; color: #475569;">Created By:</td>
                    <td>{{ $creatorName }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 600; color: #475569;">Created At:</td>
                    <td>{{ $createdAt }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 600; color: #475569;">Resolved At:</td>
                    <td>{{ $resolvedAt }}</td>
                </tr>
            </table>

            <div style="text-align: center;">
                <a href="{{ $ticketUrl }}" class="text-button">View Ticket Details</a>
            </div>

            <div class="note-box">
                <p style="margin: 0; color: #166534;">If you have any questions about the solution provided or need additional support, please don't hesitate to contact us. We're always here to help.</p>
            </div>

            <p style="font-size: 15px;">Thank you for choosing our services. We value your trust in us and look forward to continuing to support your needs.</p>
        </div>

        <div class="footer">
            <p style="margin: 0 0 15px;"><strong class="company-name">Dynamic Solutions Innovators</strong><br>
            Mohakhali DOHS, Dhaka, Bangladesh</p>

            <p style="margin: 15px 0 0; font-size: 13px; line-height: 1.5;">
                Your satisfaction is important to us. We would greatly appreciate it if you could take a moment to share your feedback about the support you received.
            </p>
            <p style="margin: 8px 0 0; font-size: 13px;">
                Thank you for allowing us to serve you. We appreciate your business and look forward to assisting you again in the future.
            </p>
        </div>
    </div>
</body>
</html>

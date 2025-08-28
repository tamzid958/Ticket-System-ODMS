<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Ticket Notification</title>
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
            background: linear-gradient(to right, #f0f9ff, #e0f2fe);
            color: #0c4a6e;
            padding: 28px 30px;
            text-align: center;
            border-bottom: 1px solid #e0f2fe;
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
            background-color: #f0f9ff;
            border-left: 4px solid #0ea5e9;
            padding: 18px;
            border-radius: 6px;
            margin: 25px 0;
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
            <h1 style="margin: 0; font-size: 28px; font-weight: 600;">ODMS Ticket Creation</h1>
            <p style="margin: 8px 0 0; font-size: 14px;">Your attention is needed for a client request</p>
        </div>
        
        <div class="email-body">
            <h3> <p>Hello Support Team,</p> </h3>
            <p style="font-size: 15px;">We wanted to gently inform you that a new support ticket has been submitted by one of our valued clients. When you have a moment, we would greatly appreciate it if you could review the details below and take the appropriate action at your earliest convenience.</p>
            
            <p style="font-size: 15px;">Your expertise and timely response are truly valued, and we know you'll provide the excellent service our clients have come to expect from us.</p>
            
            <table class="ticket-table">
                <tr>
                    <th colspan="2">Ticket Information</th>
                </tr>
                <tr>
                    <td style="width: 35%; font-weight: 600; color: #475569;">Client Name:</td>
                    <td>{{ $clientName }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 600; color: #475569;">Created By:</td>
                    <td>{{ $creatorName }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 600; color: #475569;">Title:</td>
                    <td>{{ $ticketTitle }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 600; color: #475569;">Created At:</td>
                    <td>{{ $createdAt }}</td>
                </tr>
            </table>
            
            <div style="text-align: center;">
                <a href="{{ $ticketUrl }}" class="text-button">Access Ticket Details</a>
            </div>
            
            <div class="note-box">
                <p style="margin: 0; color: #0c4a6e;">Our standard response time for new tickets is within 24 hours. If this ticket falls outside your area of expertise, we kindly ask that you reassign it to the appropriate team member.</p>
            </div>
        </div>
        
        <div class="footer">
            <p style="margin: 0 0 15px;"><strong class="company-name">Dynamic Solutions Innovators</strong><br>
            Mohakhali DOHS, Dhaka, Bangladesh</p>
            
            <p style="margin: 15px 0 0; font-size: 13px; line-height: 1.5;">
                If this ticket isn't relevant to your responsibilities, no action is required. Please avoid forwarding tickets unless they're specifically related to another team's work.
            </p>
            <p style="margin: 8px 0 0; font-size: 13px;">
                We appreciate your attention to this matter and thank you for your continued support.
            </p>
        </div>
    </div>
</body>
</html>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Team Invitation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #f9f9f9;
            border-radius: 8px;
            padding: 30px;
            border: 1px solid #ddd;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .content {
            background: #fff;
            padding: 20px;
            border-radius: 6px;
            border: 1px solid #eee;
        }
        .button {
            display: inline-block;
            background-color: #4a6cf7;
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 4px;
            margin: 20px 0;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Team Invitation</h1>
        </div>
        <div class="content">
            <p>{{ $message }}</p>

            <p style="text-align: center;">
                <a href="{{ url('/join-team/' . $token) }}" class="button">Accept Invitation</a>
            </p>

            <p>If you did not expect this invitation or have questions, please contact the sender directly.</p>

            <p>Regards,<br>{{ config('app.name') }}</p>
        </div>
        <div class="footer">
            <p>This email was sent from {{ config('app.name') }}.</p>
            <p>This is NOT a test email to verify that the mail system is working.</p>
        </div>
    </div>
</body>
</html>
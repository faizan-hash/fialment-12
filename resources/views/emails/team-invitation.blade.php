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
        .header h1 {
            color: #4a6cf7;
            margin: 0;
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
            <p>Hello,</p>
            
            <p>You have been invited by <strong>{{ $inviter->name }}</strong> to join the team <strong>{{ $team->name }}</strong> as a <strong>{{ ucfirst(str_replace('_', ' ', $invitation->role)) }}</strong>.</p>
            
            <p>About the team:</p>
            <p>{{ $team->description }}</p>
            
            <p style="text-align: center;">
                <a href="{{ $acceptUrl }}" class="button">Accept Invitation</a>
            </p>
            
            <p><strong>Note:</strong> This invitation expires on {{ $expiresAt }}.</p>
            
            <p>If you did not expect this invitation or have questions, please contact the sender directly.</p>
            
            <p>Regards,<br>The {{ config('app.name') }} Team</p>
        </div>
        <div class="footer">
            <p>This email was sent from {{ config('app.name') }}. If you have questions, please contact support.</p>
        </div>
    </div>
</body>
</html> 
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

            @php
                // Import the constants directly
                use App\Models\User;

                // Manual role determination directly from DB
                $roleDisplayName = 'Team Member';

                $roleRecords = DB::select("
                    SELECT r.name, r.id
                    FROM roles r
                    JOIN model_has_roles mhr ON r.id = mhr.role_id
                    WHERE mhr.model_id = ? AND mhr.model_type = 'App\\\\Models\\\\User'
                ", [$inviter->id]);

                $roles = [];
                foreach ($roleRecords as $role) {
                    $roles[] = $role->name;
                }

                // Check for roles in order of priority
                if (in_array('personal_coach', $roles)) {
                    $roleDisplayName = 'Personal Coach';
                }
                elseif (in_array('subject_mentor', $roles)) {
                    $roleDisplayName = 'Subject Mentor';
                }
                elseif (in_array('admin', $roles)) {
                    $roleDisplayName = 'Project Advisor';
                }
                elseif (in_array('student', $roles)) {
                    $roleDisplayName = 'Student';
                }
                elseif (!empty($roles)) {
                    // Use the first available role if none of the above match
                    $roleDisplayName = ucfirst(str_replace('_', ' ', $roles[0]));
                }
            @endphp

            <p>You have been invited by <strong>{{ $inviter->name }}</strong> ({{ $roleDisplayName }}) to join the team <strong>{{ $team->name }}</strong> as a <strong>{{ ucfirst(str_replace('_', ' ', $invitation->role)) }}</strong>.</p>

            @if(!empty($team->description))
                <p>About the team:</p>
                <p>{{ $team->description }}</p>
            @endif

            <!-- Role-specific content -->
            <div style="background-color: #f5f8ff; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #4a6cf7;">
                <h3 style="color: #4a6cf7; margin-top: 0;">{{ $roleContent['title'] }}</h3>
                <p>{{ $roleContent['description'] }}</p>

                <p><strong>Key Responsibilities:</strong></p>
                <ul>
                    @foreach($roleContent['responsibilities'] as $responsibility)
                        <li>{{ $responsibility }}</li>
                    @endforeach
                </ul>

                <!-- Role-specific instructions -->
                @if(isset($roleContent['instructions']))
                <p><strong>Getting Started Instructions:</strong></p>
                <div style="background-color: #f0f8ff; padding: 15px; border-radius: 5px; margin: 10px 0;">
                    @foreach($roleContent['instructions'] as $title => $instruction)
                        <div style="margin-bottom: 10px;">
                            <h4 style="color: #4a6cf7; margin: 0 0 5px 0;">{{ $title }}</h4>
                            <p style="margin: 0; padding-left: 10px; border-left: 2px solid #4a6cf7;">{{ $instruction }}</p>
                        </div>
                    @endforeach
                </div>
                @endif
            </div>

            <p style="text-align: center;">
                <a href="{{ $acceptUrl }}" class="button">Accept Invitation</a>
            </p>

            <p><strong>Note:</strong> This invitation expires on {{ $expiresAt }}.</p>

            <p>If you did not expect this invitation or have questions, please contact the sender directly.</p>

            <p>Regards,<br>{{ config('app.name') }}</p>
        </div>
        <div class="footer">
            <p>This email was sent from {{ config('app.name') }}. If you have questions, please contact support.</p>
        </div>
    </div>
</body>
</html>

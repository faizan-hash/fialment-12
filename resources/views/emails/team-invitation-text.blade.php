Team Invitation

Hello,

You have been invited by {{ $inviter->name }} ({{ ucfirst(str_replace('_', ' ', $inviter->roles->first()?->name ?? 'member')) }}) to join the team {{ $team->name }} as a {{ ucfirst(str_replace('_', ' ', $invitation->role)) }}.

@if(!empty($team->description))
About the team:
{{ $team->description }}
@endif

Key Responsibilities:
@foreach($roleContent['responsibilities'] as $responsibility)
- {{ $responsibility }}
@endforeach

To accept this invitation, please visit:
{{ $acceptUrl }}

Note: This invitation expires on {{ $expiresAt }}.

If you did not expect this invitation or have questions, please contact the sender directly.

Regards,
{{ config('app.name') }}

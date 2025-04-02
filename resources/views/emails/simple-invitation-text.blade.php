Team Invitation

{{ $message }}

To accept this invitation, please visit:
{{ url('/join-team/' . $token) }}

If you did not expect this invitation or have questions, please contact the sender directly.

Regards,
{{ config('app.name') }}

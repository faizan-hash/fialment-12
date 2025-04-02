<?php

namespace App\Mail;

use App\Models\TeamInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 60;

    /**
     * Unique ID for this email to prevent duplicates
     *
     * @var string
     */
    public $uniqueId;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public TeamInvitation $invitation,
    ) {
        // Generate a unique ID for this email
        $this->uniqueId = $invitation->id . '_' . time() . '_' . rand(1000, 9999);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invitation to Join Team: ' . $this->invitation->team->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        try {
            $acceptUrl = url('/join-team/' . $this->invitation->token);

            // Check that required relations are loaded
            $team = $this->invitation->team;
            $inviter = $this->invitation->inviter;

            if (!$team || !$inviter) {
                \Illuminate\Support\Facades\Log::warning('Team invitation email missing relations', [
                    'invitation_id' => $this->invitation->id,
                    'has_team' => (bool)$team,
                    'has_inviter' => (bool)$inviter,
                ]);

                // If we're missing relations, try to load them
                if (!$team) {
                    $team = \App\Models\Team::find($this->invitation->team_id);
                }

                if (!$inviter) {
                    $inviter = \App\Models\User::find($this->invitation->invited_by);
                }
            }

            // If still missing, create placeholder objects to prevent template errors
            if (!$team) {
                $team = new \App\Models\Team([
                    'name' => 'Team (Not Found)',
                    'description' => 'Team details unavailable',
                ]);
            }

            if (!$inviter) {
                $inviter = new \App\Models\User([
                    'name' => 'Team Administrator',
                    'email' => 'admin@example.com',
                ]);
            }

            // Determine role-specific content
            $roleContent = $this->getRoleSpecificContent($this->invitation->role);

            return new Content(
                view: 'emails.team-invitation',
                text: 'emails.team-invitation-text',
                with: [
                    'invitation' => $this->invitation,
                    'team' => $team,
                    'inviter' => $inviter,
                    'acceptUrl' => $acceptUrl,
                    'expiresAt' => $this->invitation->expires_at->format('F j, Y'),
                    'roleContent' => $roleContent,
                ],
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error creating team invitation email', [
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
                'invitation_id' => $this->invitation->id ?? null,
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            // Fallback to a simple content to prevent email failure
            return new Content(
                view: 'emails.simple-invitation',
                text: 'emails.simple-invitation-text',
                with: [
                    'message' => 'You have been invited to join a team. Please visit our site to respond to the invitation.',
                    'token' => $this->invitation->token ?? 'invalid-token',
                ],
            );
        }
    }

    /**
     * Get role-specific content for the email.
     */
    private function getRoleSpecificContent(string $role): array
    {
        return match ($role) {
            'student' => [
                'title' => 'Welcome to the Team as a Student',
                'description' => 'As a student, you will be able to receive feedback from mentors and coaches, track your progress, and collaborate with your team members.',
                'responsibilities' => [
                    'Complete assigned tasks',
                    'Actively participate in team discussions',
                    'Request feedback when needed',
                ],
                'instructions' => [
                    'Getting Started' => 'Log in to your account and visit the dashboard to see your assigned teams.',
                    'Submitting Work' => 'Upload your assignments through the "Submit Work" section in your dashboard. Make sure to follow the requirements provided by your mentors.',
                    'Requesting Feedback' => 'After submitting work, you can request specific feedback from your mentors or coach through the "Request Feedback" button.',
                    'Communication' => 'Use the team chat to communicate with team members, ask questions, and share resources.',
                    'Progress Tracking' => 'Monitor your progress in the "My Progress" section where you can see all feedback received and skills you\'ve developed.'
                ]
            ],
            'subject_mentor' => [
                'title' => 'Welcome to the Team as a Subject Mentor',
                'description' => 'As a subject mentor, you will provide expertise and feedback to students on project-related topics, guiding them through their learning journey and collaborating with project advisors.',
                'responsibilities' => [
                    'Provide feedback to students on submitted work',
                    'Guide students on project-related topics and challenges',
                    'Collaborate with project advisors on student development',
                    'Offer constructive evaluation of student skills',
                ],
                'instructions' => [
                    'Student Management' => 'Access your assigned students through the "My Students" section in your dashboard.',
                    'Reviewing Work' => 'When students submit work, you\'ll receive a notification. Review submissions through the "Pending Reviews" section.',
                    'Providing Feedback' => 'Use the feedback form to provide detailed comments on specific skills and practices. Include both strengths and areas for improvement.',
                    'Setting Expectations' => 'Create clear guidelines for assignments and share them with your students through the "Resources" section.',
                    'Tracking Progress' => 'Monitor student improvement over time using the "Student Progress" analytics dashboard.'
                ]
            ],
            'personal_coach' => [
                'title' => 'Welcome to the Team as a Personal Coach',
                'description' => 'As a personal coach, you will be assigned to individual students to provide personalized feedback and guidance, focusing specifically on their personal development and skill practice.',
                'responsibilities' => [
                    'Work closely with your assigned students',
                    'Provide personalized feedback tailored to each student',
                    'Focus on personal development and skill practice',
                    'Help students identify and develop their strengths',
                ],
                'instructions' => [
                    'Coaching Dashboard' => 'Find all your assigned students in the "My Coaching Students" section of your dashboard.',
                    'Student Progress' => 'Track each student\'s development through their progress reports and feedback history.',
                    'Providing Support' => 'Schedule one-on-one sessions with students using the "Schedule Meeting" feature.',
                    'Feedback Tools' => 'Give personalized feedback based on specific skills and practices through the "Give Feedback" form.',
                    'Goal Setting' => 'Help students set personal and academic goals through the "Student Goals" section and regularly review their progress.'
                ]
            ],
            default => [
                'title' => 'Welcome to the Team',
                'description' => 'You have been invited to join this team.',
                'responsibilities' => [
                    'Participate in team activities',
                    'Collaborate with team members',
                ],
                'instructions' => [
                    'Getting Started' => 'Log in to your account and visit the dashboard to see your assigned teams.',
                    'Communication' => 'Use the team chat to communicate with team members, ask questions, and share resources.',
                    'Support' => 'If you need help, contact the team administrator or use the help center for guidance.'
                ]
            ],
        };
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * The unique ID for the job to prevent duplicate processing.
     * This prevents multiple jobs for the same invitation/email combination.
     */
    public function getJobUniqueId(): string
    {
        return 'team_invitation_email_' . $this->invitation->id . '_' . $this->invitation->email;
    }

    /**
     * The number of seconds after which the job's unique lock will be released.
     */
    public function uniqueFor(): int
    {
        // Lock this job for 1 hour to prevent duplicates
        return 60 * 60; // 1 hour
    }
}

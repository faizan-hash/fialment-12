<?php

namespace App\Filament\Widgets;

use App\Models\TeamInvitation;
use App\Models\Team;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingInvitationsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';
    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        $user = auth()->user();
        
        // Admins, mentors and coaches can view pending invitations
        return $user && ($user->hasRole('admin') || 
                        $user->hasRole('subject_mentor') || 
                        $user->hasRole('personal_coach'));
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        
        // Count of all pending invitations - where not accepted, not rejected, and not expired
        $totalPending = TeamInvitation::whereNull('accepted_at')
            ->whereNull('rejected_at')
            ->where('expires_at', '>', Carbon::now())
            ->count();
        
        // Get pending invitations sent by current user
        $sentByUser = 0;
        if (!$user->hasRole('admin')) {
            $sentByUser = TeamInvitation::where('invited_by', $user->id)
                ->whereNull('accepted_at')
                ->whereNull('rejected_at')
                ->where('expires_at', '>', Carbon::now())
                ->count();
        }
        
        // Get invitations by team (for admins and mentors)
        $teamInvitations = [];
        
        if ($user->hasRole('admin')) {
            // Admin sees all teams' pending invitations
            $teamInvitations = Team::withCount(['invitations as invitations_count' => function($query) {
                    $query->whereNull('accepted_at')
                        ->whereNull('rejected_at')
                        ->where('expires_at', '>', Carbon::now());
                }])
                ->orderByDesc('invitations_count')
                ->take(3)
                ->get();
        } else if ($user->hasRole('subject_mentor') || $user->hasRole('personal_coach')) {
            // Mentors/coaches see invitations for their teams
            $teamInvitations = $user->teams()
                ->withCount(['invitations as invitations_count' => function($query) {
                    $query->whereNull('accepted_at')
                        ->whereNull('rejected_at')
                        ->where('expires_at', '>', Carbon::now());
                }])
                ->orderByDesc('invitations_count')
                ->take(3)
                ->get();
        }
        
        // Calculate total for top 3 teams
        $top3TeamsTotal = $teamInvitations->sum('invitations_count');
        
        // Initialize the stats array
        $stats = [
            Stat::make('Total Pending Invitations', $totalPending)
                ->description('All pending invites')
                ->descriptionIcon('heroicon-m-envelope')
                ->color('danger'),
        ];
        
        // Conditionally add the "Your Pending Invites" stat only for non-admin users
        if (!$user->hasRole('admin')) {
            $stats[] = Stat::make('Your Pending Invites', $sentByUser)
                ->description('Sent by you')
                ->descriptionIcon('heroicon-m-paper-airplane')
                ->color('warning');
        }
        
        // Add the teams stat
        $stats[] = Stat::make('Top Teams Invites', $top3TeamsTotal)
            ->description('For busiest teams')
            ->descriptionIcon('heroicon-m-user-group')
            ->color('primary');
            
        return $stats;
    }
} 
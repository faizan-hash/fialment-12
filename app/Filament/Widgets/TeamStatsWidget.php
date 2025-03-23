<?php

namespace App\Filament\Widgets;

use App\Models\Feedback;
use App\Models\Team;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class TeamStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';
    protected static ?int $sort = 4;

    public static function canView(): bool
    {
        $user = auth()->user();
        
        // Only for coaches, mentors, and admins
        return $user && ($user->hasRole('admin') || 
                        $user->hasRole('subject_mentor') || 
                        $user->hasRole('personal_coach'));
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        
        // For admin, show all teams stats
        // For mentors/coaches, show only their teams
        $teamsQuery = $user->hasRole('admin') 
            ? Team::query() 
            : $user->teams();
            
        $teamIds = $teamsQuery->pluck('id')->toArray();
        
        // Count team members by role
        $studentCount = 0;
        $mentorCount = 0;
        $coachCount = 0;
        
        if (!empty($teamIds)) {
            $roleCounts = DB::table('team_user')
                ->join('model_has_roles', 'team_user.user_id', '=', 'model_has_roles.model_id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->whereIn('team_user.team_id', $teamIds)
                ->select('roles.name', DB::raw('count(*) as count'))
                ->groupBy('roles.name')
                ->get()
                ->pluck('count', 'name')
                ->toArray();
                
            $studentCount = $roleCounts['student'] ?? 0;
            $mentorCount = $roleCounts['subject_mentor'] ?? 0;
            $coachCount = $roleCounts['personal_coach'] ?? 0;
        }
        
        // Get feedback count for these teams
        $teamFeedbackCount = Feedback::whereIn('team_id', $teamIds)->count();
        
        // Get avg feedback per student in these teams
        $avgFeedbackPerStudent = $studentCount > 0
            ? round($teamFeedbackCount / $studentCount, 1)
            : 0;
            
        // Get count of teams with complete mentor/coach assignments
        $completeTeamsCount = 0;
        
        if (!empty($teamIds)) {
            // Count teams that have at least one coach and one mentor
            $completeTeamsCount = DB::table('teams')
                ->whereIn('teams.id', $teamIds)
                ->where(function($query) {
                    // Check for teams with at least one mentor
                    $query->whereExists(function($subquery) {
                        $subquery->select(DB::raw(1))
                            ->from('team_user')
                            ->join('model_has_roles', 'team_user.user_id', '=', 'model_has_roles.model_id')
                            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                            ->whereRaw('team_user.team_id = teams.id')
                            ->where('roles.name', 'subject_mentor');
                    });
                })
                ->where(function($query) {
                    // Check for teams with at least one coach
                    $query->whereExists(function($subquery) {
                        $subquery->select(DB::raw(1))
                            ->from('team_user')
                            ->join('model_has_roles', 'team_user.user_id', '=', 'model_has_roles.model_id')
                            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                            ->whereRaw('team_user.team_id = teams.id')
                            ->where('roles.name', 'personal_coach');
                    });
                })
                ->count();
        }
            
        return [
            Stat::make('Students', $studentCount)
                ->description('In your teams')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),
                
            Stat::make('Mentors', $mentorCount)
                ->description('Subject mentors')
                ->descriptionIcon('heroicon-m-user')
                ->color('primary'),
                
            Stat::make('Coaches', $coachCount)
                ->description('Personal coaches')
                ->descriptionIcon('heroicon-m-user-circle')
                ->color('info'),
                
            Stat::make('Team Feedback', $teamFeedbackCount)
                ->description('Total items')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('warning'),
                
            Stat::make('Feedback/Student', $avgFeedbackPerStudent)
                ->description('Average per student')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('danger'),
                
            Stat::make('Complete Teams', $completeTeamsCount)
                ->description('With mentors & coaches')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
        ];
    }
} 
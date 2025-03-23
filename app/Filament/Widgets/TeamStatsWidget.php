<?php

namespace App\Filament\Widgets;

use App\Models\Feedback;
use App\Models\Team;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class TeamStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';
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
        $userId = $user->id;
        
        // Cache key includes user ID to ensure each user gets their own stats
        $cacheKey = "team_stats_widget_{$userId}";
        
        // Cache the stats for 5 minutes to reduce database load
        return Cache::remember($cacheKey, 300, function() use ($user) {
            // For admin, show all teams stats
            // For mentors/coaches, show only their teams
            $teamsQuery = $user->hasRole('admin') 
                ? Team::query() 
                : $user->teams();
                
            $teamIds = $teamsQuery->pluck('teams.id')->toArray();
            
            if (empty($teamIds)) {
                return $this->getEmptyStats();
            }
            
            // Use a single query to get all role counts for better performance
            $roleCounts = DB::table('team_user')
                ->join('model_has_roles', 'team_user.user_id', '=', 'model_has_roles.model_id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->whereIn('team_user.team_id', $teamIds)
                ->select('roles.name', DB::raw('count(DISTINCT team_user.user_id) as count'))
                ->groupBy('roles.name')
                ->get()
                ->pluck('count', 'name')
                ->toArray();
                
            $studentCount = $roleCounts['student'] ?? 0;
            $mentorCount = $roleCounts['subject_mentor'] ?? 0;
            $coachCount = $roleCounts['personal_coach'] ?? 0;
            
            // Get feedback count for these teams
            $teamFeedbackCount = Feedback::whereIn('team_id', $teamIds)->count();
            
            // Get avg feedback per student in these teams
            $avgFeedbackPerStudent = $studentCount > 0
                ? round($teamFeedbackCount / $studentCount, 1)
                : 0;
                
            // Get count of teams with complete mentor/coach assignments - simplified query
            $completeTeamsCount = DB::table('teams AS t')
                ->whereIn('t.id', $teamIds)
                ->where(function($query) {
                    $query->whereExists(function($q) {
                        $q->select(DB::raw(1))
                            ->from('team_user AS tu1')
                            ->join('model_has_roles AS mr1', 'tu1.user_id', '=', 'mr1.model_id')
                            ->join('roles AS r1', 'mr1.role_id', '=', 'r1.id')
                            ->whereRaw('tu1.team_id = t.id')
                            ->where('r1.name', 'subject_mentor');
                    })
                    ->whereExists(function($q) {
                        $q->select(DB::raw(1))
                            ->from('team_user AS tu2')
                            ->join('model_has_roles AS mr2', 'tu2.user_id', '=', 'mr2.model_id')
                            ->join('roles AS r2', 'mr2.role_id', '=', 'r2.id')
                            ->whereRaw('tu2.team_id = t.id')
                            ->where('r2.name', 'personal_coach');
                    });
                })
                ->count();
                
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
        });
    }
    
    /**
     * Return empty stats when no teams are available
     */
    private function getEmptyStats(): array
    {
        return [
            Stat::make('Students', 0)
                ->description('In your teams')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),
                
            Stat::make('Mentors', 0)
                ->description('Subject mentors')
                ->descriptionIcon('heroicon-m-user')
                ->color('primary'),
                
            Stat::make('Coaches', 0)
                ->description('Personal coaches')
                ->descriptionIcon('heroicon-m-user-circle')
                ->color('info'),
                
            Stat::make('Team Feedback', 0)
                ->description('Total items')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('warning'),
                
            Stat::make('Feedback/Student', 0)
                ->description('Average per student')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('danger'),
                
            Stat::make('Complete Teams', 0)
                ->description('With mentors & coaches')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
        ];
    }
} 
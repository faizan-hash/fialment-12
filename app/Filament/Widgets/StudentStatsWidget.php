<?php

namespace App\Filament\Widgets;

use App\Models\Feedback;
use App\Models\Skill;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class StudentStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';
    protected static ?int $sort = 5;

    public static function canView(): bool
    {
        $user = auth()->user();
        
        // Only students should see this widget
        return $user && $user->hasRole('student');
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $userId = $user->id;
        
        // Use caching to reduce database load
        return Cache::remember("student_stats_widget_{$userId}", 300, function() use ($user, $userId) {
            // Get user's team IDs
            $teamIds = $user->teams()->pluck('teams.id')->toArray();
            
            if (empty($teamIds)) {
                return $this->getEmptyStats();
            }
            
            // Count feedback received by skill in a single optimized query
            $skillsFeedback = Feedback::where('receiver_id', $userId)
                ->select('skill_id', DB::raw('count(*) as count'))
                ->groupBy('skill_id')
                ->pluck('count', 'skill_id')
                ->toArray();
                
            $skillsWithFeedback = count($skillsFeedback);
            $totalSkills = Cache::remember('total_skills_count', 3600, function() {
                return Skill::count();
            });
            
            // Get skill with most feedback
            $topSkillId = !empty($skillsFeedback) ? array_search(max($skillsFeedback), $skillsFeedback) : null;
            $topSkillName = 'None';
            $topSkillCount = 0;
            
            if ($topSkillId) {
                $topSkill = Cache::remember("skill_{$topSkillId}", 3600, function() use ($topSkillId) {
                    return Skill::find($topSkillId);
                });
                
                if ($topSkill) {
                    $topSkillName = $topSkill->name;
                    $topSkillCount = $skillsFeedback[$topSkillId];
                }
            }
            
            // Count coaches and mentors in a more efficient way
            $coachesCount = $user->personalCoaches()->count();
            
            // Use a more efficient query to count mentors in user's teams
            $mentorsCount = User::role('subject_mentor')
                ->whereHas('teams', function($query) use ($teamIds) {
                    $query->whereIn('teams.id', $teamIds);
                })
                ->count();
                
            // Get feedback stats with optimized query
            $userFeedbackCount = Feedback::where('receiver_id', $userId)->count();
            
            // Only compute this expensive query if user has received feedback
            $feedbackPercentile = 0;
            
            if ($userFeedbackCount > 0) {
                // Use a cached value for average student feedback to reduce load
                $avgStudentFeedback = Cache::remember('avg_student_feedback', 900, function() {
                    return DB::table('feedback AS f')
                        ->join('model_has_roles AS mr', 'f.receiver_id', '=', 'mr.model_id')
                        ->join('roles AS r', 'mr.role_id', '=', 'r.id')
                        ->where('r.name', 'student')
                        ->select('f.receiver_id', DB::raw('count(*) as count'))
                        ->groupBy('f.receiver_id')
                        ->avg('count') ?: 0;
                });
                
                $feedbackPercentile = $avgStudentFeedback > 0 
                    ? round(min(100, ($userFeedbackCount / $avgStudentFeedback) * 100))
                    : 0;
            }
                
            return [
                Stat::make('Your Feedback', $userFeedbackCount)
                    ->description('Items received')
                    ->descriptionIcon('heroicon-m-inbox')
                    ->color('success'),
                    
                Stat::make("Student Percentile", "{$feedbackPercentile}%")
                    ->description('Compared to peers')
                    ->descriptionIcon('heroicon-m-chart-bar')
                    ->color('primary'),
                    
                Stat::make('Top Skill', $topSkillName)
                    ->description("{$topSkillCount} feedback items")
                    ->descriptionIcon('heroicon-m-academic-cap')
                    ->color('warning'),
                    
                Stat::make('Skills With Feedback', $skillsWithFeedback)
                    ->description("Out of {$totalSkills} total")
                    ->descriptionIcon('heroicon-m-document-check')
                    ->color('info'),
                    
                Stat::make('Personal Coaches', $coachesCount)
                    ->description('Assigned to you')
                    ->descriptionIcon('heroicon-m-user-circle')
                    ->color('danger'),
                    
                Stat::make('Subject Mentors', $mentorsCount)
                    ->description('In your teams')
                    ->descriptionIcon('heroicon-m-user')
                    ->color('gray'),
            ];
        });
    }
    
    /**
     * Return empty stats when no teams are available
     */
    private function getEmptyStats(): array
    {
        $totalSkills = Cache::remember('total_skills_count', 3600, function() {
            return Skill::count();
        });
        
        return [
            Stat::make('Your Feedback', 0)
                ->description('Items received')
                ->descriptionIcon('heroicon-m-inbox')
                ->color('success'),
                
            Stat::make("Student Percentile", "0%")
                ->description('Compared to peers')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),
                
            Stat::make('Top Skill', 'None')
                ->description("0 feedback items")
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('warning'),
                
            Stat::make('Skills With Feedback', 0)
                ->description("Out of {$totalSkills} total")
                ->descriptionIcon('heroicon-m-document-check')
                ->color('info'),
                
            Stat::make('Personal Coaches', 0)
                ->description('Assigned to you')
                ->descriptionIcon('heroicon-m-user-circle')
                ->color('danger'),
                
            Stat::make('Subject Mentors', 0)
                ->description('In your teams')
                ->descriptionIcon('heroicon-m-user')
                ->color('gray'),
        ];
    }
} 
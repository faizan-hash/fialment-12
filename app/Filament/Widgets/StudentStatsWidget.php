<?php

namespace App\Filament\Widgets;

use App\Models\Feedback;
use App\Models\Skill;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StudentStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';
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
        
        // Count feedback received by skill
        $skillsFeedback = Feedback::where('receiver_id', $user->id)
            ->select('skill_id', DB::raw('count(*) as count'))
            ->groupBy('skill_id')
            ->pluck('count', 'skill_id')
            ->toArray();
            
        $skillsWithFeedback = count($skillsFeedback);
        
        // Get skill with most feedback
        $topSkillId = !empty($skillsFeedback) ? array_search(max($skillsFeedback), $skillsFeedback) : null;
        $topSkillName = $topSkillId ? Skill::find($topSkillId)->name : 'None';
        $topSkillCount = $topSkillId ? $skillsFeedback[$topSkillId] : 0;
        
        // Count coaches and mentors assigned to student
        $coachesCount = $user->personalCoaches()->count();
        $mentorsCount = User::role('subject_mentor')
            ->whereHas('teams', function($query) use ($user) {
                $query->whereIn('teams.id', $user->teams()->pluck('teams.id'));
            })
            ->count();
            
        // Get average feedback compared to other students
        $studentFeedbackCounts = DB::table('feedback')
            ->join('model_has_roles', 'feedback.receiver_id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'student')
            ->select('feedback.receiver_id', DB::raw('count(*) as count'))
            ->groupBy('feedback.receiver_id')
            ->pluck('count');
            
        $avgStudentFeedback = $studentFeedbackCounts->isEmpty() ? 0 : $studentFeedbackCounts->avg();
            
        $userFeedbackCount = Feedback::where('receiver_id', $user->id)->count();
        $feedbackPercentile = $avgStudentFeedback > 0 
            ? round(min(100, ($userFeedbackCount / $avgStudentFeedback) * 100))
            : 0;
            
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
                ->description('Out of ' . Skill::count() . ' total')
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
    }
} 
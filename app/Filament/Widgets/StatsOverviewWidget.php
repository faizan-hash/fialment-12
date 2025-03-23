<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Team;
use App\Models\Feedback;
use App\Models\Skill;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        // Only admins and advisors can view this system-wide widget
        $user = auth()->user();
        return $user && ($user->hasRole('admin') || $user->hasRole('subject_mentor'));
    }

    protected function getStats(): array
    {
        // Get current counts
        $userCount = User::count();
        $teamCount = Team::count();
        $feedbackCount = Feedback::count();
        $skillCount = Skill::count();
        
        // Get recent feedback counts
        $today = Carbon::today();
        $lastWeek = Carbon::now()->subDays(7);
        $lastMonth = Carbon::now()->subDays(30);
        
        $feedbackLastWeek = Feedback::where('created_at', '>=', $lastWeek)->count();
        $feedbackLastMonth = Feedback::where('created_at', '>=', $lastMonth)->count();
        
        // Calculate averages
        $avgFeedbackPerUser = $userCount > 0 ? round($feedbackCount / $userCount, 1) : 0;
        $avgTeamSize = $teamCount > 0 ? round(User::whereHas('teams')->count() / $teamCount, 1) : 0;
        
        return [
            Stat::make('Total Users', $userCount)
                ->description('All registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Total Teams', $teamCount)
                ->description('Total team count')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning'),

            Stat::make('Feedback Items', $feedbackCount)
                ->description('Total feedback provided')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('primary'),
                
            Stat::make('Feedback This Week', $feedbackLastWeek)
                ->description('Last 7 days')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('danger'),
                
            Stat::make('Skills Tracked', $skillCount)
                ->description('Available skills')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),
                
            Stat::make('Avg. Feedback/User', $avgFeedbackPerUser)
                ->description('Engagement metric')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),
                
            Stat::make('Avg. Team Size', $avgTeamSize)
                ->description('Members per team')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning'),
        ];
    }
} 
<?php

namespace App\Filament\Widgets;

use App\Models\Feedback;
use App\Models\Team;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        // All users should see their own stats
        return auth()->check();
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        
        // Count feedback given and received
        $feedbackSent = Feedback::where('sender_id', $user->id)->count();
        $feedbackReceived = Feedback::where('receiver_id', $user->id)->count();
        
        // Count teams and team members
        $teamsCount = $user->teams()->count();
        $teammatesCount = 0;
        
        if ($teamsCount > 0) {
            $teamIds = $user->teams()->pluck('teams.id')->toArray();
            $teammatesCount = Team::whereIn('id', $teamIds)
                ->withCount('users')
                ->get()
                ->sum('users_count');
            
            // Don't count the user multiple times if they're in multiple teams
            $teammatesCount = max(0, $teammatesCount - $teamsCount);
        }
        
        // Calculate ratios
        $responseRate = $feedbackReceived > 0 
            ? round(($feedbackSent / $feedbackReceived) * 100)
            : 0;
            
        return [
            Stat::make('Feedback Given', $feedbackSent)
                ->description('Total sent')
                ->descriptionIcon('heroicon-m-paper-airplane')
                ->color('success'),
                
            Stat::make('Feedback Received', $feedbackReceived)
                ->description('Total received')
                ->descriptionIcon('heroicon-m-inbox')
                ->color('info'),
                
            Stat::make('Teams', $teamsCount)
                ->description('Team membership')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning'),
                
            Stat::make('Teammates', $teammatesCount)
                ->description('Connected users')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
        ];
    }
} 
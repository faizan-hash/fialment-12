<?php

namespace App\Filament\Pages;

use App\Models\Feedback;
use App\Models\Skill;
use App\Models\SkillArea;
use App\Models\Team;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    
    public function getTitle(): string
    {
        return 'Skills Dashboard';
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_team')
                ->label('Create Project Team')
                ->url(route('filament.admin.resources.teams.create'))
                ->icon('heroicon-m-user-group')
                ->color('primary'),
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            CurrentStrengthsWidget::class,
            SkillsToPracticeWidget::class,
        ];
    }
    
    protected function getFooterWidgets(): array
    {
        $widgets = [
            \App\Filament\Widgets\StatsOverviewWidget::class,
        ];
        
        if (auth()->user()->hasRole('student')) {
            $widgets[] = \App\Filament\Widgets\StudentStatsWidget::class;
        }
        
        if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('teacher')) {
            $widgets[] = \App\Filament\Widgets\TeamStatsWidget::class;
            $widgets[] = \App\Filament\Widgets\UserStatsWidget::class;
        }
        
        return $widgets;
    }
}

class CurrentStrengthsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;
    
    protected function getStats(): array
    {
        $user = Auth::user();
        
        // Get skills with positive feedback
        $skillsWithPositiveFeedback = Skill::whereHas('practices.feedback', function ($query) use ($user) {
            $query->where('recipient_id', $user->id)
                ->where('is_positive', true);
        })
        ->withCount(['practices as feedback_count' => function ($query) use ($user) {
            $query->whereHas('feedback', function ($subQuery) use ($user) {
                $subQuery->where('recipient_id', $user->id)
                    ->where('is_positive', true);
            });
        }])
        ->orderBy('feedback_count', 'desc')
        ->with('skillArea')
        ->take(3)
        ->get();
        
        return $skillsWithPositiveFeedback->map(function ($skill) {
            return Stat::make($skill->name)
                ->description('From ' . $skill->skillArea->name . ' area')
                ->value($skill->feedback_count . ' positive feedback')
                ->color('success')
                ->chart([1, 2, 3, $skill->feedback_count]);
        })->toArray();
    }
}

class SkillsToPracticeWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;
    
    protected function getStats(): array
    {
        $user = Auth::user();
        
        // Get skills with negative or no feedback
        $skillsToPractice = Skill::whereDoesntHave('practices.feedback', function ($query) use ($user) {
            $query->where('recipient_id', $user->id)
                ->where('is_positive', true);
        })
        ->orWhereHas('practices.feedback', function ($query) use ($user) {
            $query->where('recipient_id', $user->id)
                ->where('is_positive', false);
        })
        ->withCount(['practices as feedback_count' => function ($query) use ($user) {
            $query->whereHas('feedback', function ($subQuery) use ($user) {
                $subQuery->where('recipient_id', $user->id);
            });
        }])
        ->with('skillArea')
        ->take(3)
        ->get();
        
        return $skillsToPractice->map(function ($skill) {
            return Stat::make($skill->name)
                ->description('From ' . $skill->skillArea->name . ' area')
                ->value('Needs practice')
                ->color('danger')
                ->url(route('filament.admin.resources.skills.view', ['record' => $skill->id]));
        })->toArray();
    }
} 
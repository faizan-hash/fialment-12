<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamInvitationResource\Pages;
use App\Filament\Resources\TeamInvitationResource\RelationManagers;
use App\Models\TeamInvitation;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\BaseResource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Filament\Notifications\Notification;

class TeamInvitationResource extends BaseResource
{
    protected static ?string $model = TeamInvitation::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'Team Invitations';

    protected static ?string $navigationGroup = 'Team Management';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('team_id')
                    ->label('Team')
                    ->options(Team::pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('invite_type', null)),
                Forms\Components\Select::make('invite_type')
                    ->label('Invitation Type')
                    ->options([
                        'single' => 'Single Role',
                        'bulk' => 'Bulk Invite',
                    ])
                    ->required()
                    ->reactive()
                    ->default('single'),
                Forms\Components\Select::make('role')
                    ->options([
                        'student' => 'Student',
                        'subject_mentor' => 'Subject Mentor',
                        'personal_coach' => 'Personal Coach',
                    ])
                    ->required()
                    ->visible(fn (callable $get) => $get('invite_type') === 'single'),
                Forms\Components\Select::make('bulk_option')
                    ->label('Bulk Invitation Option')
                    ->options(function (callable $get) {
                        if (!$get('team_id') || $get('invite_type') !== 'bulk') {
                            return [];
                        }

                        $team = Team::find($get('team_id'));
                        if (!$team) return [];

                        $options = ['all' => 'All Team Members'];

                        // Check if team has students
                        $studentsCount = $team->users()->role('student')->count();
                        if ($studentsCount > 0) {
                            $options['student'] = "Students ({$studentsCount})";
                        }

                        // Check if team has subject mentors
                        $mentorsCount = $team->users()->role('subject_mentor')->count();
                        if ($mentorsCount > 0) {
                            $options['subject_mentor'] = "Subject Mentors ({$mentorsCount})";
                        }

                        // Check if team has personal coaches
                        $coachesCount = $team->users()->role('personal_coach')->count();
                        if ($coachesCount > 0) {
                            $options['personal_coach'] = "Personal Coaches ({$coachesCount})";
                        }

                        return $options;
                    })
                    ->visible(fn (callable $get) => $get('invite_type') === 'bulk')
                    ->required(fn (callable $get) => $get('invite_type') === 'bulk'),
                Forms\Components\Hidden::make('email')
                    ->dehydrateStateUsing(function (callable $get) {
                        if ($get('invite_type') !== 'single') {
                            return 'bulk_invite@placeholder.com';
                        }

                        // Get the team
                        $team = Team::find($get('team_id'));
                        if (!$team) {
                            return 'team_not_found@placeholder.com';
                        }

                        $role = $get('role');

                        // Find users with the selected role
                        $users = $team->users()->role($role)->get();

                        // Return first user's email or placeholder
                        return $users->isNotEmpty() ? $users->first()->email : 'no_users_found@placeholder.com';
                    })
                    ->visible(fn (callable $get) => $get('invite_type') === 'single'),
                Forms\Components\Hidden::make('expires_at')
                    ->default(Carbon::now()->addDays(7)),
                Forms\Components\Hidden::make('invited_by')
                    ->dehydrateStateUsing(fn () => Auth::id()),
                Forms\Components\Hidden::make('token')
                    ->dehydrateStateUsing(fn () => Str::random(64)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('team.name')
                    ->label('Team')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->getStateUsing(function (TeamInvitation $record) {
                        // Try to find the real user email from users table
                        $user = User::where('email', $record->email)->first();
                        // Return user email if found, otherwise the invitation email
                        return $user ? $user->email : $record->email;
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'student' => 'primary',
                        'subject_mentor' => 'warning',
                        'personal_coach' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'student' => 'Student',
                        'subject_mentor' => 'Subject Mentor',
                        'personal_coach' => 'Personal Coach',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (TeamInvitation $record): string => match (true) {
                        $record->isAccepted() => 'success',
                        $record->isRejected() => 'danger',
                        $record->isExpired() => 'gray',
                        $record->isPending() => 'warning',
                        default => 'gray',
                    })
                    ->getStateUsing(fn (TeamInvitation $record): string => match (true) {
                        $record->isAccepted() => 'Accepted',
                        $record->isRejected() => 'Rejected',
                        $record->isExpired() => 'Expired',
                        $record->isPending() => 'Pending',
                        default => 'Unknown',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'student' => 'Student',
                        'subject_mentor' => 'Subject Mentor',
                        'personal_coach' => 'Personal Coach',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'accepted' => 'Accepted',
                        'rejected' => 'Rejected',
                        'expired' => 'Expired',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return match($data['value']) {
                            'pending' => $query->whereNull('accepted_at')
                                ->whereNull('rejected_at')
                                ->where('expires_at', '>', Carbon::now()),
                            'accepted' => $query->whereNotNull('accepted_at'),
                            'rejected' => $query->whereNotNull('rejected_at'),
                            'expired' => $query->whereNull('accepted_at')
                                ->whereNull('rejected_at')
                                ->where('expires_at', '<=', Carbon::now()),
                        };
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('resend')
                    ->label('Resend')
                    ->icon('heroicon-o-envelope')
                    ->button()
                    ->color('warning')
                    ->visible(fn (TeamInvitation $record) => $record->isPending())
                    ->action(function (TeamInvitation $record) {
                        // Find the team
                        $team = Team::find($record->team_id);

                        if (!$team) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Failed to Resend')
                                ->body('Team not found.')
                                ->send();
                            return;
                        }

                        // Find all users with the selected role in this team
                        $users = $team->users()->role($record->role)->get();
                        $count = 0;

                        foreach ($users as $user) {
                            if (!$user->email) continue;

                            // Use cache to prevent duplicate emails
                            $cacheKey = 'invitation_email_sent_' . $record->id . '_' . $user->id . '_' . time();

                            // Send the invitation email only if not cached
                            if (!\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                                \Illuminate\Support\Facades\Mail::to($user->email)
                                    ->queue(new \App\Mail\TeamInvitationMail($record));

                                // Mark as processed (1 hour cache)
                                \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addHour());

                                $count++;
                            }
                        }

                        if ($count > 0) {
                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Invitation Resent')
                                ->body("Invitation resent to {$count} users with the role {$record->role}")
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Failed to Resend')
                                ->body('No users found with this role in the team.')
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeamInvitations::route('/'),
            'create' => Pages\CreateTeamInvitation::route('/create'),
            'edit' => Pages\EditTeamInvitation::route('/{record}/edit'),
        ];
    }
}

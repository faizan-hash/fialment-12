<?php

namespace App\Filament\Resources\TeamResource\RelationManagers;

use App\Models\TeamInvitation;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class InvitationsRelationManager extends RelationManager
{
    protected static string $relationship = 'invitations';

    protected static ?string $title = 'Team Invitations';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('role')
                    ->options([
                        'student' => 'Student',
                        'subject_mentor' => 'Subject Mentor',
                        'personal_coach' => 'Personal Coach',
                    ])
                    ->required()
                    ->reactive(),
                Forms\Components\Hidden::make('email')
                    ->dehydrateStateUsing(function (callable $get) {
                        // Get the team
                        $team = $this->getOwnerRecord();
                        $role = $get('role');

                        // Find users with the selected role
                        $users = $team->users()->role($role)->get();

                        // Return first user's email or placeholder
                        return $users->isNotEmpty() ? $users->first()->email : 'no_users_found@placeholder.com';
                    }),
                Forms\Components\DateTimePicker::make('expires_at')
                    ->label('Expires At')
                    ->default(Carbon::now()->addDays(7))
                    ->required(),
                Forms\Components\Hidden::make('invited_by')
                    ->dehydrateStateUsing(fn () => auth()->id()),
                Forms\Components\Hidden::make('token')
                    ->dehydrateStateUsing(fn () => Str::random(64)),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('email')
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->getStateUsing(function (TeamInvitation $record) {
                        // Try to find the real user email from users table
                        $user = \App\Models\User::where('email', $record->email)->first();
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
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Invite New Member')
                    ->after(function (TeamInvitation $record) {
                        // Get the team from the relation
                        $team = $this->getOwnerRecord();

                        // Set skip_observer_email to true to prevent duplicate emails from observer
                        $record->setSkipObserverEmail(true);
                        $record->save();

                        // Find all users with the selected role in this team
                        $users = $team->users()->role($record->role)->get();
                        $count = 0;

                        foreach ($users as $user) {
                            if (!$user->email) continue;

                            // Use a stable cache key without the time component
                            $cacheKey = 'invitation_email_sent_' . $record->id . '_' . $user->id;

                            // Only send if not already processed in the last hour
                            if (!\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                                // Send the invitation email
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
                                ->title('Success')
                                ->body("Invitation sent to {$count} users with the role {$record->role}")
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->warning()
                                ->title('Warning')
                                ->body('No users found with this role in the team.')
                                ->send();
                        }
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
                        // Get the team
                        $team = $this->getOwnerRecord();

                        // Set skip_observer_email to true to prevent duplicate emails from observer
                        $record->setSkipObserverEmail(true);
                        $record->save();

                        // Find all users with the selected role in this team
                        $users = $team->users()->role($record->role)->get();
                        $count = 0;

                        foreach ($users as $user) {
                            if (!$user->email) continue;

                            // For explicit resends, use a cache key with a timestamp suffix to allow resending
                            // but still prevent duplicates from the same resend action
                            $resendTimestamp = time();
                            $cacheKey = 'invitation_email_resent_' . $record->id . '_' . $user->id . '_' . $resendTimestamp;

                            // Only send if not already processed in the last minute
                            if (!\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                                // Send the invitation email
                                \Illuminate\Support\Facades\Mail::to($user->email)
                                    ->queue(new \App\Mail\TeamInvitationMail($record));

                                // Mark as processed (1 minute cache to prevent double-clicks)
                                \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addMinute());

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
}

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
use Illuminate\Support\Str;

class InvitationsRelationManager extends RelationManager
{
    protected static string $relationship = 'invitations';
    
    protected static ?string $title = 'Team Invitations';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('role')
                    ->options([
                        'student' => 'Student',
                        'subject_mentor' => 'Subject Mentor',
                        'personal_coach' => 'Personal Coach',
                    ])
                    ->required(),
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
                Tables\Columns\TextColumn::make('inviter.name')
                    ->label('Invited By')
                    ->searchable(),
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
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
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
                    ->label('Invite New Member'),
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
                        // Actually send the invitation email
                        \Illuminate\Support\Facades\Mail::to($record->email)
                            ->send(new \App\Mail\TeamInvitationMail($record));
                            
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Invitation Resent')
                            ->body('An invitation email has been resent to ' . $record->email)
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}

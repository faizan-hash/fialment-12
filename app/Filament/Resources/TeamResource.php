<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamResource\Pages;
use App\Filament\Resources\TeamResource\RelationManagers;
use App\Models\Team;
use App\Filament\BaseResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class TeamResource extends BaseResource
{
    protected static ?string $model = Team::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Teams';
    protected static ?string $navigationGroup = 'Team Management';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Section::make('Team Members')
                    ->schema([
                        Forms\Components\Select::make('students')
                            ->label('Students')
                            ->multiple()
                            ->relationship(
                                'users',
                                'name',
                                fn (Builder $query) => $query->role('student')
                            )
                            ->preload()
                            ->searchable()
                            ->placeholder('Select students')
                            ->noSearchResultsMessage('No students found'),
                        Forms\Components\Select::make('subject_mentors')
                            ->label('Subject Mentors')
                            ->multiple()
                            ->relationship(
                                'users',
                                'name',
                                fn (Builder $query) => $query->role('subject_mentor')
                            )
                            ->preload()
                            ->searchable()
                            ->placeholder('Select subject mentors')
                            ->noSearchResultsMessage('No subject mentors found'),
                        Forms\Components\Select::make('personal_coaches')
                            ->label('Personal Coaches')
                            ->multiple()
                            ->relationship(
                                'users',
                                'name',
                                fn (Builder $query) => $query->role('personal_coach')
                            )
                            ->preload()
                            ->searchable()
                            ->placeholder('Select personal coaches')
                            ->noSearchResultsMessage('No personal coaches found'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable()
                    ->default(fn() => Auth::user()->name)
                    ->placeholder('Project Advisor'),
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Members'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            RelationManagers\UsersRelationManager::make(),
            RelationManagers\InvitationsRelationManager::make(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeams::route('/'),
            'create' => Pages\CreateTeam::route('/create'),
            'edit' => Pages\EditTeam::route('/{record}/edit'),
        ];
    }
}

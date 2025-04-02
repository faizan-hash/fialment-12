<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CoachStudentResource\Pages;
use App\Filament\Resources\CoachStudentResource\RelationManagers;
use App\Models\CoachStudent;
use App\Models\User;
use App\Filament\BaseResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CoachStudentResource extends BaseResource
{
    protected static ?string $model = CoachStudent::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Coach Assignments';
    protected static ?string $modelLabel = 'Coach-Student Assignment';
    protected static ?string $navigationGroup = 'Team Management';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('team_id')
                    ->relationship('team', 'name')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, $set) {
                        // When team changes, reset the student_id value
                        $set('student_id', null);
                        $set('coach_id', null);
                    }),
                Forms\Components\Select::make('coach_id')
                    ->relationship(
                        'coach', 
                        'name', 
                        fn (Builder $query, $get) => $query
                            ->role('personal_coach')
                            ->whereHas('teams', fn (Builder $q) => 
                                $q->where('teams.id', $get('team_id'))
                            )
                    )
                    ->preload()
                    ->searchable()
                    ->required()
                    ->label('Coach'),
                Forms\Components\Select::make('student_id')
                    ->relationship(
                        'student', 
                        'name', 
                        fn (Builder $query, $get) => $query
                            ->role('student')
                            ->whereHas('teams', fn (Builder $q) => 
                                $q->where('teams.id', $get('team_id'))
                            )
                    )
                    ->preload()
                    ->searchable()
                    ->required()
                    ->label('Student'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('team.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('coach.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListCoachStudents::route('/'),
            'create' => Pages\CreateCoachStudent::route('/create'),
            'edit' => Pages\EditCoachStudent::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CoachAssignmentResource\Pages;
use App\Filament\Resources\CoachAssignmentResource\RelationManagers;
use App\Models\User;
use App\Models\CoachStudent;
use App\Filament\BaseResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CoachAssignmentResource extends BaseResource
{
    protected static ?string $model = CoachStudent::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationLabel = 'Coach Assignments';
    
    protected static ?string $modelLabel = 'Coach Assignment';
    
    protected static ?string $pluralModelLabel = 'Coach Assignments';
    
    protected static ?string $navigationGroup = 'Team Management';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('coach_id')
                    ->label('Personal Coach')
                    ->options(
                        User::role('personal_coach')->pluck('name', 'id')
                    )
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('student_id')
                    ->label('Student')
                    ->options(
                        User::role('student')->pluck('name', 'id')
                    )
                    ->searchable()
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('coach.name')
                    ->label('Personal Coach')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('notes')
                    ->limit(50)
                    ->searchable(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoachAssignments::route('/'),
            'create' => Pages\CreateCoachAssignment::route('/create'),
            'edit' => Pages\EditCoachAssignment::route('/{record}/edit'),
        ];
    }
}

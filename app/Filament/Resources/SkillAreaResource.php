<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SkillAreaResource\Pages;
use App\Filament\Resources\SkillAreaResource\RelationManagers;
use App\Models\SkillArea;
use App\Filament\BaseResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SkillAreaResource extends BaseResource
{
    protected static ?string $model = SkillArea::class;

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';
    protected static ?string $navigationLabel = 'Skill Areas';
    protected static ?string $navigationGroup = 'Skills & Practices';
    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\ColorPicker::make('color')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\ColorColumn::make('color')
                    ->searchable(),
                Tables\Columns\TextColumn::make('skills_count')
                    ->counts('skills')
                    ->label('Skills')
                    ->sortable(),
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
            RelationManagers\SkillsRelationManager::make(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSkillAreas::route('/'),
            'create' => Pages\CreateSkillArea::route('/create'),
            'edit' => Pages\EditSkillArea::route('/{record}/edit'),
        ];
    }
}
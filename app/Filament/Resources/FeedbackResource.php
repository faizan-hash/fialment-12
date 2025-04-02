<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeedbackResource\Pages;
use App\Filament\Resources\FeedbackResource\RelationManagers;
use App\Models\Feedback;
use App\Filament\BaseResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class FeedbackResource extends BaseResource
{
    protected static ?string $model = Feedback::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Feedback';
    protected static ?string $navigationGroup = 'Feedback Management';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Feedback Details')
                    ->schema([
                        Forms\Components\Select::make('sender_id')
                            ->relationship('sender', 'name')
                            ->label('From')
                            ->default(fn() => Auth::check() ? Auth::id() : null)
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        Forms\Components\Select::make('receiver_id')
                            ->relationship('recipient', 'name')
                            ->label('To')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->placeholder('Select recipient')
                            ->noSearchResultsMessage('No users found'),
                        Forms\Components\Select::make('team_id')
                            ->relationship('team', 'name')
                            ->label('Project Team')
                            ->required()
                            ->reactive()
                            ->searchable()
                            ->preload()
                            ->placeholder('Select team')
                            ->noSearchResultsMessage('No teams found'),
                        Forms\Components\Toggle::make('is_positive')
                            ->label('Is Positive Feedback')
                            ->helperText('Toggle this if the feedback is positive, leave it off if it\'s an area for improvement')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Skill & Practice')
                    ->schema([
                        Forms\Components\Select::make('skill_id')
                            ->relationship('skill', 'name', function (Builder $query) {
                                return $query->with('skillArea')->orderBy('name');
                            })
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->skillArea->name})")
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('practice_id', null))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->placeholder('Select skill')
                            ->noSearchResultsMessage('No skills found'),
                        Forms\Components\Select::make('practice_id')
                            ->relationship('practice', 'description', fn (Builder $query, callable $get) =>
                                $query->where('skill_id', $get('skill_id'))
                            )
                            ->required()
                            ->preload()
                            ->searchable()
                            ->placeholder(function (callable $get) {
                                if ($get('skill_id')) {
                                    return 'No practices available for this skill';
                                }

                                return 'Select a practice';
                            })
                            ->searchable()
                            ->searchDebounce(500)
                            ->searchingMessage('Searching practices...')
                            ->noSearchResultsMessage('No matching practices found'),
                        Forms\Components\Textarea::make('comments')
                            ->placeholder('Provide specific details about what was observed')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sender.name')
                    ->label('From')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('recipient.name')
                    ->label('To')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_positive')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->label('Positive'),
                Tables\Columns\TextColumn::make('team.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('skill.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('practice.description')
                    ->limit(30)
                    ->tooltip(fn (Feedback $record): ?string => $record->practice?->description)
                    ->searchable(),
                Tables\Columns\TextColumn::make('comments')
                    ->limit(30)
                    ->tooltip(fn (Feedback $record): ?string => $record->comments)
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('team')
                    ->relationship('team', 'name'),
                Tables\Filters\SelectFilter::make('skill')
                    ->relationship('skill', 'name'),
                Tables\Filters\TernaryFilter::make('is_positive')
                    ->label('Feedback Type')
                    ->placeholder('All Feedback')
                    ->trueLabel('Positive Feedback')
                    ->falseLabel('Areas for Improvement'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListFeedback::route('/'),
            'create' => Pages\CreateFeedback::route('/create'),
            'edit' => Pages\EditFeedback::route('/{record}/edit'),
        ];
    }
}

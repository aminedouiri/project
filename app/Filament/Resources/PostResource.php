<?php

namespace App\Filament\Resources;

use livewire;
use stdClass;
use Filament\Forms;
use App\Models\Post;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use App\Tables\Columns\TitleSwitcher;
use FilamentTiptapEditor\TiptapEditor;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Database\Eloquent\Builder;
use FilamentTiptapEditor\Enums\TiptapOutput;
use Filament\Resources\Concerns\Translatable;
use Filament\Tables\Columns\SpatieTagsColumn;
use App\Filament\Resources\PostResource\Pages;
use Filament\Forms\Components\SpatieTagsInput;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PostResource\RelationManagers;
use Illuminate\Database\Eloquent\Collection;

class PostResource extends Resource
{
    use Translatable;

    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live(debounce: 700)
                    ->afterStateUpdated(function (Set $set, Get $get, $livewire) {
                        if($livewire->activeLocale === 'en_US') {
                            $string = preg_replace('/\s+/', ' ', $get('title'));
                            $string = strtolower(str_replace(' ', '-', $string));
                            $slug = trim($string, '-');
                            $count = Post::where('slug', 'LIKE', $slug.'-%')
                                    ->whereRaw("slug REGEXP ?", ['^' . preg_quote($slug) . "-[0-9]+$"])
                                    ->count();
                            if ($count > 0) {
                                while (Post::where('slug', $slug)->exists()) {
                                    $count++;
                                    $slug = $slug . '-' . ($count);
                                }
                            }
                            else if(Post::where('slug', $slug)->exists()) {
                                $slug = $slug.'-1';
                            }
                            $set('slug', $slug);
                        }
                    }),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->readonly()
                    ->validationMessages(['required' => 'Enter the title in English. The slug will be auto-generated.']),
                TiptapEditor::make('content')
                    ->extraInputAttributes(['style' => 'min-height: 12rem;'])
                    ->required()
                    ->columnSpanFull()
                    ->output(TiptapOutput::Html),
                Forms\Components\DateTimePicker::make('start_at')
                    ->required()
                    ->native(false)
                    ->placeholder('dd/mm/yyyy --:--:--')
                    ->displayFormat('d/m/Y H:i:s'),
                Forms\Components\DateTimePicker::make('end_at')
                    ->required()
                    ->native(false)
                    ->placeholder('dd/mm/yyyy --:--:--')
                    ->displayFormat('d/m/Y H:i:s')
                    ->after('start_at'),
                Forms\Components\DateTimePicker::make('published_at')
                    ->required()
                    ->native(false)
                    ->placeholder('dd/mm/yyyy --:--:--')
                    ->displayFormat('d/m/Y H:i:s'),
                Forms\Components\Toggle::make('published')
                    ->required()
                    ->inline(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_at')
                    ->dateTime()
                    ->sortable()
                    ->datetime('d/m/Y H:i')
                    ->wrap()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('end_at')
                    ->dateTime()
                    ->sortable()
                    ->datetime('d/m/Y H:i')
                    ->wrap()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\IconColumn::make('published')
                    ->boolean()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->datetime('d/m/Y H:i')
                    ->wrap()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->datetime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->datetime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                    Tables\Actions\ViewAction::make()->iconButton(),
                    Tables\Actions\EditAction::make()->iconButton(),
                    Tables\Actions\DeleteAction::make()->iconButton()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('Set as Published')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['published' => true]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('Set as Unpublished')
                        ->icon('heroicon-o-x-circle')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['published' => false]))
                        ->deselectRecordsAfterCompletion()
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
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            // 'view' => Pages\ViewPost::route('/{record}'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}

<?php

namespace Cskiller\FilamentIdGenerator\Filament\Resources;

use BackedEnum;
use Cskiller\FilamentIdGenerator\Filament\Resources\IdGenerationBatchResource\Pages\ListIdGenerationBatches;
use Cskiller\FilamentIdGenerator\Filament\Resources\IdGenerationBatchResource\Pages\ViewIdGenerationBatch;
use Cskiller\FilamentIdGenerator\FilamentIdGeneratorPlugin;
use Cskiller\FilamentIdGenerator\Models\IdGenerationBatch;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class IdGenerationBatchResource extends Resource
{
    protected static ?string $model = IdGenerationBatch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    public static function getNavigationGroup(): ?string
    {
        return FilamentIdGeneratorPlugin::get()->getNavigationGroup();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('template.name')->label('Template')->searchable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('completed_count'),
                TextColumn::make('failed_count'),
                TextColumn::make('updated_at')->since(),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('downloadArchive')
                    ->visible(fn (IdGenerationBatch $record): bool => filled($record->archive_path))
                    ->action(fn (IdGenerationBatch $record) => response()->download(Storage::disk($record->archive_disk)->path($record->archive_path))),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIdGenerationBatches::route('/'),
            'view' => ViewIdGenerationBatch::route('/{record}'),
        ];
    }
}

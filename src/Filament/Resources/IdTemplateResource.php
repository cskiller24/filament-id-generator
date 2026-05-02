<?php

namespace Cskiller\FilamentIdGenerator\Filament\Resources;

use BackedEnum;
use Cskiller\FilamentIdGenerator\Actions\ImportTemplateAssetsAction;
use Cskiller\FilamentIdGenerator\Filament\Resources\IdTemplateResource\Pages\CreateIdTemplate;
use Cskiller\FilamentIdGenerator\Filament\Resources\IdTemplateResource\Pages\EditIdTemplate;
use Cskiller\FilamentIdGenerator\Filament\Resources\IdTemplateResource\Pages\EditIdTemplateLayout;
use Cskiller\FilamentIdGenerator\Filament\Resources\IdTemplateResource\Pages\ListIdTemplates;
use Cskiller\FilamentIdGenerator\FilamentIdGeneratorPlugin;
use Cskiller\FilamentIdGenerator\Models\IdTemplate;
use Cskiller\FilamentIdGenerator\Support\IdDataSourceRegistry;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class IdTemplateResource extends Resource
{
    protected static ?string $model = IdTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    public static function getNavigationGroup(): ?string
    {
        return FilamentIdGeneratorPlugin::get()->getNavigationGroup();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(255),
            Select::make('target_type')
                ->options(fn (): array => app(IdDataSourceRegistry::class)->options())
                ->required(),
            TextInput::make('width_mm')
                ->numeric()
                ->required(),
            TextInput::make('height_mm')
                ->numeric()
                ->required(),
            Toggle::make('is_active')
                ->default(true),
            FileUpload::make('front_source_upload')
                ->label('Front source file')
                ->acceptedFileTypes(['image/png', 'image/jpeg', 'application/pdf'])
                ->disk((string) config('filament-id-generator.template_disk'))
                ->dehydrated(false),
            FileUpload::make('back_source_upload')
                ->label('Back source file')
                ->acceptedFileTypes(['image/png', 'image/jpeg'])
                ->disk((string) config('filament-id-generator.template_disk'))
                ->dehydrated(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('target_type')->badge(),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('updated_at')->since(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIdTemplates::route('/'),
            'create' => CreateIdTemplate::route('/create'),
            'edit' => EditIdTemplate::route('/{record}/edit'),
            'layout' => EditIdTemplateLayout::route('/{record}/layout'),
        ];
    }

    public static function maybeImportUploads(IdTemplate $record, array $data): void
    {
        $frontUpload = static::normalizeUploadPayload($data['front_source_upload'] ?? null);
        $backUpload = static::normalizeUploadPayload($data['back_source_upload'] ?? null);

        if (! $frontUpload instanceof UploadedFile) {
            return;
        }

        app(ImportTemplateAssetsAction::class)->handle(
            $record,
            $frontUpload,
            $backUpload instanceof UploadedFile ? $backUpload : null,
        );
    }

    private static function normalizeUploadPayload(mixed $upload): ?UploadedFile
    {
        if ($upload instanceof UploadedFile) {
            return $upload;
        }

        if (is_array($upload)) {
            $upload = Arr::first($upload);
        }

        if (! is_string($upload) || $upload === '') {
            return null;
        }

        $diskName = (string) config('filament-id-generator.template_disk');
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($diskName);
        $candidates = [$upload, 'livewire-tmp/' . $upload];

        foreach ($candidates as $path) {
            if (! $disk->exists($path)) {
                continue;
            }

            $absolutePath = $disk->path($path);
            $mimeType = $disk->mimeType($path) ?: null;

            return new UploadedFile(
                $absolutePath,
                basename($path),
                $mimeType,
                null,
                true,
            );
        }

        return null;
    }
}

<?php

namespace Cskiller\FilamentIdGenerator\Filament\Resources\IdTemplateResource\Pages;

use Cskiller\FilamentIdGenerator\Actions\StartIdGenerationBatchAction;
use Cskiller\FilamentIdGenerator\Filament\Resources\IdGenerationBatchResource;
use Cskiller\FilamentIdGenerator\Filament\Resources\IdTemplateResource;
use Cskiller\FilamentIdGenerator\Support\IdDataSourceRegistry;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class EditIdTemplate extends EditRecord
{
    protected static string $resource = IdTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('layout')
                ->label('Edit layout')
                ->url(fn (): string => route('id-templates.editor', $this->record))
                ->openUrlInNewTab(),
            Action::make('generate')
                ->schema([
                    Select::make('record_ids')
                        ->label('Records')
                        ->multiple()
                        ->searchable()
                        ->options(function (): array {
                            $adapter = app(IdDataSourceRegistry::class)->for($this->record->target_type);

                            return $adapter->query()
                                ->limit(200)
                                ->get()
                                ->mapWithKeys(fn ($record): array => [$record->getKey() => $adapter->label($record)])
                                ->all();
                        })
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $operator = Auth::user();

                    if (! $operator instanceof Model) {
                        return;
                    }

                    $recordIds = collect($data['record_ids'] ?? [])->map(fn ($id): int => (int) $id)->all();

                    $batch = app(StartIdGenerationBatchAction::class)->handle($this->record, $recordIds, $operator);

                    Notification::make()
                        ->title('Generation started')
                        ->success()
                        ->body(sprintf('Batch #%d was queued.', $batch->id))
                        ->send();

                    $this->redirect(IdGenerationBatchResource::getUrl('view', ['record' => $batch]));
                }),
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        IdTemplateResource::maybeImportUploads($this->record, $this->data);
    }
}

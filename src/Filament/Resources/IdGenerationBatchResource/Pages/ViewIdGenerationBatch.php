<?php

namespace Cskiller\FilamentIdGenerator\Filament\Resources\IdGenerationBatchResource\Pages;

use Cskiller\FilamentIdGenerator\Filament\Resources\IdGenerationBatchResource;
use Cskiller\FilamentIdGenerator\Jobs\RenderGeneratedIdJob;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewIdGenerationBatch extends ViewRecord
{
    protected static string $resource = IdGenerationBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('retryFailed')
                ->visible(fn (): bool => $this->record->failed_count > 0)
                ->action(function (): void {
                    $this->record->assets()
                        ->where('status', 'failed')
                        ->get()
                        ->each(fn ($asset) => RenderGeneratedIdJob::dispatch($this->record->id, $asset->source_type, $asset->source_id));
                }),
        ];
    }
}

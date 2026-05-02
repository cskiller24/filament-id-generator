<?php

namespace Cskiller\FilamentIdGenerator\Filament\Resources\IdTemplateResource\Pages;

use Cskiller\FilamentIdGenerator\Filament\Resources\IdTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateIdTemplate extends CreateRecord
{
    protected static string $resource = IdTemplateResource::class;

    protected function afterCreate(): void
    {
        IdTemplateResource::maybeImportUploads($this->record, $this->data);
    }
}

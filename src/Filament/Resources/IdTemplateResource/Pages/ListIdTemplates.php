<?php

namespace Cskiller\FilamentIdGenerator\Filament\Resources\IdTemplateResource\Pages;

use Cskiller\FilamentIdGenerator\Filament\Resources\IdTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIdTemplates extends ListRecords
{
    protected static string $resource = IdTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

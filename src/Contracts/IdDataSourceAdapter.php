<?php

namespace Cskiller\FilamentIdGenerator\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface IdDataSourceAdapter
{
    public function modelClass(): string;

    public function fields(): array;

    public function sampleValues(): array;

    public function query(): Builder;

    public function resolve(Model $record, string $mappingKey): mixed;

    public function label(Model $record): string;
}

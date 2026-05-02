<?php

namespace Cskiller\FilamentIdGenerator\Tests\Fixtures;

use Cskiller\FilamentIdGenerator\Contracts\IdDataSourceAdapter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TestIdDataSourceAdapter implements IdDataSourceAdapter
{
    public function modelClass(): string
    {
        return TestIdSourceRecord::class;
    }

    public function fields(): array
    {
        return [
            'full_name' => ['label' => 'Full name', 'type' => 'text'],
            'email' => ['label' => 'Email', 'type' => 'text'],
            'avatar' => ['label' => 'Avatar', 'type' => 'image'],
        ];
    }

    public function sampleValues(): array
    {
        return [
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'avatar' => null,
        ];
    }

    public function query(): Builder
    {
        return TestIdSourceRecord::query();
    }

    public function resolve(Model $record, string $mappingKey): mixed
    {
        return $record->getAttribute(match ($mappingKey) {
            'full_name' => 'name',
            'avatar' => 'avatar_path',
            default => $mappingKey,
        });
    }

    public function label(Model $record): string
    {
        return (string) ($record->name ?? $record->getKey());
    }
}

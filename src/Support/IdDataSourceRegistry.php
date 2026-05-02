<?php

namespace Cskiller\FilamentIdGenerator\Support;

use Cskiller\FilamentIdGenerator\Contracts\IdDataSourceAdapter;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

class IdDataSourceRegistry
{
    public function __construct(private Container $container) {}

    public function for(string $key): IdDataSourceAdapter
    {
        $class = config("filament-id-generator.adapters.{$key}");

        if (! is_string($class)) {
            throw new InvalidArgumentException("Unknown ID data source [{$key}].");
        }

        return $this->container->make($class);
    }

    public function options(): array
    {
        return collect(config('filament-id-generator.adapters', []))
            ->mapWithKeys(fn (string $class, string $key): array => [$key => str($key)->headline()->toString()])
            ->all();
    }
}

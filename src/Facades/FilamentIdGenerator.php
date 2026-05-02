<?php

namespace Cskiller\FilamentIdGenerator\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Cskiller\FilamentIdGenerator\FilamentIdGenerator
 */
class FilamentIdGenerator extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Cskiller\FilamentIdGenerator\FilamentIdGenerator::class;
    }
}

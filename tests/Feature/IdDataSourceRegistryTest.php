<?php

namespace Cskiller\FilamentIdGenerator\Tests\Feature;

use Cskiller\FilamentIdGenerator\Support\IdDataSourceRegistry;
use Cskiller\FilamentIdGenerator\Tests\Fixtures\TestIdDataSourceAdapter;
use Cskiller\FilamentIdGenerator\Tests\TestCase;
use InvalidArgumentException;

class IdDataSourceRegistryTest extends TestCase
{
    public function test_registry_resolves_known_adapter(): void
    {
        $adapter = app(IdDataSourceRegistry::class)->for('user');

        $this->assertInstanceOf(TestIdDataSourceAdapter::class, $adapter);
    }

    public function test_registry_throws_for_unknown_adapter(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(IdDataSourceRegistry::class)->for('unknown');
    }

    public function test_registry_options_returns_headlined_keys(): void
    {
        $options = app(IdDataSourceRegistry::class)->options();

        $this->assertSame(['user' => 'User'], $options);
    }
}

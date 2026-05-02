<?php

namespace Cskiller\FilamentIdGenerator\Tests\Feature;

use Cskiller\FilamentIdGenerator\FilamentIdGeneratorPlugin;
use Cskiller\FilamentIdGenerator\Tests\Fixtures\TestIdDataSourceAdapter;
use Cskiller\FilamentIdGenerator\Tests\TestCase;

class FilamentIdGeneratorPluginTest extends TestCase
{
    public function test_fluent_configuration_updates_plugin_state(): void
    {
        $plugin = FilamentIdGeneratorPlugin::make()
            ->adapters(['user' => TestIdDataSourceAdapter::class])
            ->templateResource(false)
            ->batchResource(false)
            ->navigationGroup('Custom Group');

        $this->assertSame(['user' => TestIdDataSourceAdapter::class], $plugin->getAdapters());
        $this->assertFalse($plugin->isTemplateResourceEnabled());
        $this->assertFalse($plugin->isBatchResourceEnabled());
        $this->assertSame('Custom Group', $plugin->getNavigationGroup());
    }
}

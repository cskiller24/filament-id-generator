<?php

namespace Cskiller\FilamentIdGenerator\Tests\Feature;

use Cskiller\FilamentIdGenerator\Tests\Fixtures\TestIdSourceRecord;
use Cskiller\FilamentIdGenerator\Tests\TestCase;

class PluginConfigurationTest extends TestCase
{
    public function test_config_has_expected_defaults(): void
    {
        $this->assertEquals('filament-id-generator-templates', config('filament-id-generator.template_disk'));
        $this->assertEquals('filament-id-generator-outputs', config('filament-id-generator.output_disk'));
        $this->assertEquals(150, config('filament-id-generator.preview_dpi'));
        $this->assertEquals(300, config('filament-id-generator.render_dpi'));
        $this->assertNull(config('filament-id-generator.default_font'));
        $this->assertNull(config('filament-id-generator.default_bold_font'));
        $this->assertEquals(TestIdSourceRecord::class, config('filament-id-generator.initiated_by_model'));
        $this->assertIsArray(config('filament-id-generator.adapters'));
        $this->assertArrayHasKey('user', config('filament-id-generator.adapters'));
    }

    public function test_template_disk_is_registered(): void
    {
        $this->assertNotNull(config('filesystems.disks.filament-id-generator-templates'));
        $this->assertEquals('local', config('filesystems.disks.filament-id-generator-templates.driver'));
    }

    public function test_output_disk_is_registered(): void
    {
        $this->assertNotNull(config('filesystems.disks.filament-id-generator-outputs'));
        $this->assertEquals('local', config('filesystems.disks.filament-id-generator-outputs.driver'));
    }
}

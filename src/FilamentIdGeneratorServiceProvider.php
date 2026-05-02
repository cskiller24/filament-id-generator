<?php

namespace Cskiller\FilamentIdGenerator;

use Cskiller\FilamentIdGenerator\Support\IdDataSourceRegistry;
use Cskiller\FilamentIdGenerator\Testing\TestsFilamentIdGenerator;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Filesystem\Filesystem;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentIdGeneratorServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-id-generator';

    public static string $viewNamespace = 'filament-id-generator';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasConfigFile('filament-id-generator')
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('cskiller/filament-id-generator');
            });

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(IdDataSourceRegistry::class);
    }

    public function packageBooted(): void
    {
        // Register storage disks
        config()->set('filesystems.disks.filament-id-generator-templates', [
            'driver' => 'local',
            'root' => storage_path('app/filament-id-generator/templates'),
            'throw' => false,
        ]);

        config()->set('filesystems.disks.filament-id-generator-outputs', [
            'driver' => 'local',
            'root' => storage_path('app/filament-id-generator/outputs'),
            'throw' => false,
        ]);

        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Handle Stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/filament-id-generator/{$file->getFilename()}"),
                ], 'filament-id-generator-stubs');
            }
        }

        // Testing
        Testable::mixin(new TestsFilamentIdGenerator);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'cskiller/filament-id-generator';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            Js::make('editor', __DIR__ . '/../resources/dist/editor.js'),
            Css::make('editor', __DIR__ . '/../resources/dist/editor.css'),
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_id_templates_table',
            'create_id_template_sides_table',
            'create_id_template_fields_table',
            'create_id_generation_batches_table',
            'create_generated_id_assets_table',
        ];
    }
}

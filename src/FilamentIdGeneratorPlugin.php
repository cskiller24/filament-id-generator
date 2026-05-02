<?php

namespace Cskiller\FilamentIdGenerator;

use Cskiller\FilamentIdGenerator\Filament\Resources\IdGenerationBatchResource;
use Cskiller\FilamentIdGenerator\Filament\Resources\IdTemplateResource;
use Cskiller\FilamentIdGenerator\Http\Controllers\EditorController;
use Filament\Contracts\Plugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Panel;
use Illuminate\Support\Facades\Route;

class FilamentIdGeneratorPlugin implements Plugin
{
    protected array $adapters = [];

    protected bool $templateResourceEnabled = true;

    protected bool $batchResourceEnabled = true;

    protected ?string $navigationGroup = 'ID Generator';

    public function getId(): string
    {
        return 'filament-id-generator';
    }

    public function register(Panel $panel): void
    {
        if ($this->templateResourceEnabled) {
            $panel->resources([
                IdTemplateResource::class,
            ]);
        }

        if ($this->batchResourceEnabled) {
            $panel->resources([
                IdGenerationBatchResource::class,
            ]);
        }

        $existing = config('filament-id-generator.adapters', []);
        config()->set('filament-id-generator.adapters', array_merge($existing, $this->adapters));
    }

    public function boot(Panel $panel): void
    {
        $prefix = trim($panel->getPath(), '/');

        Route::middleware(['web', Authenticate::class])
            ->prefix($prefix)
            ->group(function (): void {
                Route::get('/id-templates/{idTemplate}/editor', [EditorController::class, 'show'])
                    ->name('id-templates.editor');

                Route::post('/api/id-templates/{idTemplate}/layout', [EditorController::class, 'save'])
                    ->name('id-templates.layout.save');
            });
    }

    public function adapters(array $adapters): static
    {
        $this->adapters = $adapters;

        return $this;
    }

    public function templateResource(bool $enabled = true): static
    {
        $this->templateResourceEnabled = $enabled;

        return $this;
    }

    public function batchResource(bool $enabled = true): static
    {
        $this->batchResourceEnabled = $enabled;

        return $this;
    }

    public function navigationGroup(string $group): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    public function getNavigationGroup(): ?string
    {
        return $this->navigationGroup;
    }

    public function getAdapters(): array
    {
        return $this->adapters;
    }

    public function isTemplateResourceEnabled(): bool
    {
        return $this->templateResourceEnabled;
    }

    public function isBatchResourceEnabled(): bool
    {
        return $this->batchResourceEnabled;
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}

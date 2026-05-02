<?php

namespace Cskiller\FilamentIdGenerator\Filament\Resources\IdTemplateResource\Pages;

use Cskiller\FilamentIdGenerator\Filament\Resources\IdTemplateResource;
use Cskiller\FilamentIdGenerator\Support\IdDataSourceRegistry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * @deprecated Superseded by the standalone editor view.
 */
class EditIdTemplateLayout extends Page
{
    use InteractsWithRecord;

    protected static string $resource = IdTemplateResource::class;

    protected string $view = 'filament-id-generator::filament.resources.id-template-resource.pages.edit-id-template-layout';

    public array $layouts = [];

    public string $activeSide = '';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $template = $this->getRecord()->load('sides.fields');

        foreach ($template->sides as $side) {
            $this->layouts[$side->side->value] = $side->fields->map(fn ($field): array => [
                'label' => $field->label,
                'type' => $field->type->value,
                'mapping_key' => $field->mapping_key,
                'x' => $field->x,
                'y' => $field->y,
                'width' => $field->width,
                'height' => $field->height,
                'z_index' => $field->z_index,
                'is_required' => $field->is_required,
                'default_value' => $field->default_value,
                'properties' => $field->properties ?? [],
            ])->values()->all();
        }

        $sides = $this->getSidesProperty();
        $this->activeSide = $sides[0]['key'] ?? 'front';
    }

    public function setSide(string $key): void
    {
        $this->activeSide = $key;
    }

    public function saveLayout(): void
    {
        Validator::make(['layouts' => $this->layouts], [
            'layouts.*.*.type' => ['required', 'in:text,image'],
            'layouts.*.*.label' => ['required', 'string', 'max:255'],
            'layouts.*.*.x' => ['required', 'integer', 'min:0'],
            'layouts.*.*.y' => ['required', 'integer', 'min:0'],
            'layouts.*.*.width' => ['required', 'integer', 'min:1'],
            'layouts.*.*.height' => ['required', 'integer', 'min:1'],
        ])->validate();

        DB::transaction(function (): void {
            $this->getRecord()->load('sides.fields');

            foreach ($this->getRecord()->sides as $side) {
                $payload = $this->layouts[$side->side->value] ?? [];
                $side->fields()->delete();

                foreach ($payload as $index => $field) {
                    $side->fields()->create([
                        'label' => $field['label'],
                        'type' => $field['type'],
                        'mapping_key' => $field['mapping_key'] ?? null,
                        'x' => (int) $field['x'],
                        'y' => (int) $field['y'],
                        'width' => (int) $field['width'],
                        'height' => (int) $field['height'],
                        'z_index' => (int) ($field['z_index'] ?? $index),
                        'is_required' => (bool) ($field['is_required'] ?? false),
                        'default_value' => $field['default_value'] ?? null,
                        'properties' => $field['properties'] ?? [],
                    ]);
                }
            }
        });

        Notification::make()->title('Layout saved')->success()->send();
    }

    public function getSidesProperty(): array
    {
        return $this->getRecord()->sides
            ->map(function ($side): array {
                /** @var FilesystemAdapter $disk */
                $disk = Storage::disk($side->preview_disk);
                $previewUrl = $side->preview_path ? $disk->url($side->preview_path) : null;

                return [
                    'key' => $side->side->value,
                    'label' => ucfirst($side->side->value),
                    'preview_url' => $previewUrl,
                    'canvas_width' => $side->canvas_width,
                    'canvas_height' => $side->canvas_height,
                ];
            })
            ->all();
    }

    public function getAdapterFieldsProperty(): array
    {
        return app(IdDataSourceRegistry::class)->for($this->getRecord()->target_type)->fields();
    }

    public function getSampleValuesProperty(): array
    {
        return app(IdDataSourceRegistry::class)->for($this->getRecord()->target_type)->sampleValues();
    }
}

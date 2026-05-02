<?php

namespace Cskiller\FilamentIdGenerator\Http\Controllers;

use Cskiller\FilamentIdGenerator\Filament\Resources\IdTemplateResource;
use Cskiller\FilamentIdGenerator\Models\IdTemplate;
use Cskiller\FilamentIdGenerator\Support\IdDataSourceRegistry;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EditorController
{
    public function show(IdTemplate $idTemplate): View
    {
        $template = $idTemplate->load('sides.fields');
        $adapter = app(IdDataSourceRegistry::class)->for($template->target_type);

        $sides = $template->sides
            ->sortBy(fn ($side) => $side->side->value === 'front' ? 0 : 1)
            ->map(function ($side): array {
                $disk = $side->preview_disk ?: config('filament-id-generator.template_disk');
                /** @var FilesystemAdapter $filesystem */
                $filesystem = Storage::disk($disk);
                $previewUrl = $side->preview_path ? $filesystem->url($side->preview_path) : null;

                return [
                    'key' => $side->side->value,
                    'label' => ucfirst($side->side->value),
                    'preview_url' => $previewUrl,
                    'canvas_width' => $side->canvas_width,
                    'canvas_height' => $side->canvas_height,
                ];
            })
            ->values()
            ->all();

        $initialLayouts = [];

        foreach ($template->sides as $side) {
            $initialLayouts[$side->side->value] = $side->fields->map(fn ($field): array => [
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

        $bootstrap = [
            'templateId' => $template->id,
            'templateName' => $template->name,
            'adapterFields' => $adapter->fields(),
            'sides' => $sides,
            'initialLayouts' => $initialLayouts,
            'sampleValues' => $adapter->sampleValues(),
            'saveUrl' => route('id-templates.layout.save', $template),
            'backUrl' => IdTemplateResource::getUrl('edit', ['record' => $template]),
        ];

        return view('filament-id-generator::editor.id-template', [
            'template' => $template,
            'bootstrap' => $bootstrap,
        ]);
    }

    public function save(Request $request, IdTemplate $idTemplate): JsonResponse
    {
        $validated = $request->validate([
            'side' => ['required', 'string', 'in:front,back'],
            'fields' => ['present', 'array'],
            'fields.*.label' => ['required', 'string', 'max:255'],
            'fields.*.type' => ['required', 'string', 'in:text,image'],
            'fields.*.mapping_key' => ['nullable', 'string', 'max:255'],
            'fields.*.x' => ['required', 'integer', 'min:0'],
            'fields.*.y' => ['required', 'integer', 'min:0'],
            'fields.*.width' => ['required', 'integer', 'min:1'],
            'fields.*.height' => ['required', 'integer', 'min:1'],
            'fields.*.z_index' => ['sometimes', 'integer', 'min:0'],
            'fields.*.is_required' => ['sometimes', 'boolean'],
            'fields.*.default_value' => ['nullable'],
            'fields.*.properties' => ['nullable', 'array'],
        ]);

        $idTemplate->load('sides');

        $matchingSide = $idTemplate->sides->first(
            fn ($side) => $side->side->value === $validated['side']
        );

        if (! $matchingSide) {
            return response()->json(['error' => 'Side not found'], 422);
        }

        DB::transaction(function () use ($matchingSide, $validated): void {
            $matchingSide->fields()->delete();

            foreach ($validated['fields'] as $index => $field) {
                $matchingSide->fields()->create([
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
        });

        return response()->json(['success' => true]);
    }
}

<?php

namespace Cskiller\FilamentIdGenerator\Actions;

use Cskiller\FilamentIdGenerator\Contracts\IdDataSourceAdapter;
use Cskiller\FilamentIdGenerator\Enums\IdImageFitMode;
use Cskiller\FilamentIdGenerator\Enums\IdTemplateFieldType;
use Cskiller\FilamentIdGenerator\Enums\IdTextOverflowMode;
use Cskiller\FilamentIdGenerator\Models\IdTemplateField;
use Cskiller\FilamentIdGenerator\Models\IdTemplateSide;
use Cskiller\FilamentIdGenerator\Support\IdDataSourceRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Typography\FontFactory;

class RenderTemplateSideAction
{
    public function __construct(private IdDataSourceRegistry $registry) {}

    public function handle(IdTemplateSide $side, Model $record): string
    {
        $outputDisk = (string) config('filament-id-generator.output_disk', 'filament-id-generator-outputs');
        $adapter = $this->registry->for($side->template->target_type);
        $manager = new ImageManager(new Driver);
        $background = $manager->read(Storage::disk($side->preview_disk)->path($side->preview_path));

        foreach ($side->fields()->orderBy('z_index')->get() as $field) {
            if ($field->type === IdTemplateFieldType::Text) {
                $this->drawTextField($manager, $background, $field, (string) ($this->resolve($adapter, $record, $field) ?? ''));

                continue;
            }

            $this->drawImageField($manager, $background, $field, $this->resolve($adapter, $record, $field));
        }

        $outputPath = sprintf(
            'id-generator/renders/%d/%s/%s.png',
            $side->id_template_id,
            $record->getMorphClass(),
            md5($side->getKey() . '|' . $record->getKey())
        );

        $absoluteOutputPath = Storage::disk($outputDisk)->path($outputPath);

        if (! is_dir(dirname($absoluteOutputPath))) {
            mkdir(dirname($absoluteOutputPath), 0777, true);
        }

        $background->toPng()->save($absoluteOutputPath);

        return $outputPath;
    }

    private function resolve(IdDataSourceAdapter $adapter, Model $record, IdTemplateField $field): mixed
    {
        $resolved = $field->mapping_key ? $adapter->resolve($record, $field->mapping_key) : null;

        return $resolved ?? $field->default_value;
    }

    private function drawTextField(ImageManager $manager, ImageInterface $background, IdTemplateField $field, string $text): void
    {
        $properties = $field->properties ?? [];
        $textStyle = is_array($properties['textStyle'] ?? null) ? $properties['textStyle'] : [];
        $fontPath = $properties['font'] ?? config('filament-id-generator.default_font');
        $size = (int) ($properties['size'] ?? 16);
        $align = $properties['align'] ?? 'left';
        $lineHeight = (float) ($properties['line_height'] ?? 1.25);
        $overflowMode = $properties['overflow_mode'] ?? IdTextOverflowMode::Wrap->value;
        $color = $textStyle['textColor'] ?? $properties['color'] ?? '#000000';

        $textLayer = $manager->create($field->width, $field->height)->fill('transparent');

        if (($textStyle['hasBackground'] ?? false) && is_string($textStyle['backgroundColor'] ?? null)) {
            $textLayer->fill($textStyle['backgroundColor']);
        }

        $textLayer->text($text, 0, 0, function (FontFactory $font) use ($fontPath, $size, $align, $lineHeight, $field, $color): void {
            if (is_string($fontPath) && $fontPath !== '') {
                $font->filename($fontPath);
            }

            $font->size($size);
            $font->color($color);
            $font->align($align);
            $font->valign('top');
            $font->lineHeight($lineHeight);
            $font->wrap($field->width);
        });

        if ($overflowMode === IdTextOverflowMode::Clip->value) {
            $textLayer->crop($field->width, $field->height, 0, 0);
        }

        $background->place($textLayer, 'top-left', $field->x, $field->y);
    }

    private function drawImageField(ImageManager $manager, ImageInterface $background, IdTemplateField $field, mixed $value): void
    {
        if (! is_string($value) || $value === '') {
            if (! isset($field->properties['fallback_path'])) {
                return;
            }

            $value = $field->properties['fallback_path'];
        }

        $image = $manager->read($value);

        match ($field->properties['fit_mode'] ?? IdImageFitMode::Contain->value) {
            IdImageFitMode::Cover->value => $image->cover($field->width, $field->height),
            IdImageFitMode::Stretch->value => $image->resize($field->width, $field->height),
            default => $image->contain($field->width, $field->height),
        };

        $background->place($image, 'top-left', $field->x, $field->y);
    }
}

<?php

namespace Cskiller\FilamentIdGenerator\Actions;

use Cskiller\FilamentIdGenerator\Enums\IdTemplateSideType;
use Cskiller\FilamentIdGenerator\Models\IdTemplate;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use InvalidArgumentException;
use RuntimeException;

class ImportTemplateAssetsAction
{
    public function __construct(
        private RasterizePdfPageAction $rasterizePdfPage,
    ) {}

    public function handle(IdTemplate $template, UploadedFile $frontUpload, ?UploadedFile $backUpload = null): void
    {
        if (strtolower($frontUpload->getClientOriginalExtension()) === 'pdf') {
            if ($backUpload !== null) {
                throw new InvalidArgumentException('Mixed-source templates are not supported.');
            }

            $this->importPdf($template, $frontUpload);

            return;
        }

        $this->importImageSide($template, $frontUpload, IdTemplateSideType::Front);

        if ($backUpload !== null) {
            $this->importImageSide($template, $backUpload, IdTemplateSideType::Back);
        }
    }

    private function importPdf(IdTemplate $template, UploadedFile $upload): void
    {
        $templateDisk = (string) config('filament-id-generator.template_disk', 'filament-id-generator-templates');
        $sourcePath = $upload->store("id-generator/templates/{$template->id}/sources", $templateDisk);
        $absoluteSourcePath = Storage::disk($templateDisk)->path($sourcePath);
        $pageCount = $this->rasterizePdfPage->pageCount($absoluteSourcePath);

        if (! in_array($pageCount, [1, 2], true)) {
            throw new InvalidArgumentException('PDF templates must contain one or two pages.');
        }

        $template->sides()->delete();

        $this->createSideFromPdf($template, IdTemplateSideType::Front, $sourcePath, $absoluteSourcePath, 1);

        if ($pageCount === 2) {
            $this->createSideFromPdf($template, IdTemplateSideType::Back, $sourcePath, $absoluteSourcePath, 2);
        }
    }

    private function createSideFromPdf(
        IdTemplate $template,
        IdTemplateSideType $side,
        string $sourcePath,
        string $absoluteSourcePath,
        int $pageNumber,
    ): void {
        $templateDisk = (string) config('filament-id-generator.template_disk', 'filament-id-generator-templates');
        $previewPath = "id-generator/templates/{$template->id}/previews/{$side->value}.png";
        $absolutePreviewPath = Storage::disk($templateDisk)->path($previewPath);

        if (! is_dir(dirname($absolutePreviewPath))) {
            mkdir(dirname($absolutePreviewPath), 0777, true);
        }

        $this->rasterizePdfPage->handle(
            absolutePdfPath: $absoluteSourcePath,
            pageNumber: $pageNumber,
            absoluteOutputPath: $absolutePreviewPath,
            resolution: (int) config('filament-id-generator.preview_dpi')
        );

        $size = getimagesize($absolutePreviewPath);

        if (! is_array($size)) {
            throw new RuntimeException('Failed to determine preview dimensions.');
        }

        [$width, $height] = $size;

        $template->sides()->create([
            'side' => $side,
            'source_disk' => $templateDisk,
            'source_path' => $sourcePath,
            'source_mime_type' => 'application/pdf',
            'preview_disk' => $templateDisk,
            'preview_path' => $previewPath,
            'page_number' => $pageNumber,
            'canvas_width' => $width,
            'canvas_height' => $height,
        ]);
    }

    private function importImageSide(IdTemplate $template, UploadedFile $upload, IdTemplateSideType $side): void
    {
        $templateDisk = (string) config('filament-id-generator.template_disk', 'filament-id-generator-templates');
        $manager = new ImageManager(new Driver);
        $sourcePath = $upload->store("id-generator/templates/{$template->id}/sources", $templateDisk);
        $previewPath = "id-generator/templates/{$template->id}/previews/{$side->value}.png";
        $absolutePreviewPath = Storage::disk($templateDisk)->path($previewPath);

        if (! is_dir(dirname($absolutePreviewPath))) {
            mkdir(dirname($absolutePreviewPath), 0777, true);
        }

        $image = $manager->read(Storage::disk($templateDisk)->path($sourcePath));
        $image->toPng()->save($absolutePreviewPath);

        $template->sides()->updateOrCreate(
            ['side' => $side->value],
            [
                'source_disk' => $templateDisk,
                'source_path' => $sourcePath,
                'source_mime_type' => $upload->getMimeType() ?? 'image/png',
                'preview_disk' => $templateDisk,
                'preview_path' => $previewPath,
                'page_number' => null,
                'canvas_width' => $image->width(),
                'canvas_height' => $image->height(),
            ]
        );
    }
}

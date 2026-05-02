<?php

namespace Cskiller\FilamentIdGenerator\Actions;

use Cskiller\FilamentIdGenerator\Enums\IdGenerationStatus;
use Cskiller\FilamentIdGenerator\Enums\IdTemplateSideType;
use Cskiller\FilamentIdGenerator\Models\GeneratedIdAsset;
use Cskiller\FilamentIdGenerator\Models\IdGenerationBatch;
use Illuminate\Database\Eloquent\Model;

class RenderGeneratedIdAction
{
    public function __construct(
        private RenderTemplateSideAction $renderTemplateSide,
        private BuildGeneratedPdfAction $buildGeneratedPdf,
    ) {}

    public function handle(IdGenerationBatch $batch, Model $record): GeneratedIdAsset
    {
        $template = $batch->template()->with('sides.fields')->firstOrFail();

        $front = $template->sides->firstWhere('side', IdTemplateSideType::Front);
        $back = $template->sides->firstWhere('side', IdTemplateSideType::Back);

        $frontPngPath = $front ? $this->renderTemplateSide->handle($front, $record) : null;
        $backPngPath = $back ? $this->renderTemplateSide->handle($back, $record) : null;
        $pdfPath = sprintf('id-generator/outputs/%d/%d/%s/%s/card.pdf', $template->id, $batch->id, $record->getMorphClass(), $record->getKey());

        $this->buildGeneratedPdf->handle($template, array_filter([$frontPngPath, $backPngPath]), $pdfPath);

        $outputDisk = (string) config('filament-id-generator.output_disk', 'filament-id-generator-outputs');

        return GeneratedIdAsset::updateOrCreate(
            [
                'id_generation_batch_id' => $batch->id,
                'source_type' => $record->getMorphClass(),
                'source_id' => $record->getKey(),
            ],
            [
                'status' => IdGenerationStatus::Completed,
                'front_png_disk' => $frontPngPath ? $outputDisk : null,
                'front_png_path' => $frontPngPath,
                'back_png_disk' => $backPngPath ? $outputDisk : null,
                'back_png_path' => $backPngPath,
                'pdf_disk' => $outputDisk,
                'pdf_path' => $pdfPath,
                'error_message' => null,
            ]
        );
    }
}

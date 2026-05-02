<?php

namespace Cskiller\FilamentIdGenerator\Actions;

use Cskiller\FilamentIdGenerator\Models\IdGenerationBatch;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BuildBatchArchiveAction
{
    public function handle(IdGenerationBatch $batch): string
    {
        $outputDisk = (string) config('filament-id-generator.output_disk', 'filament-id-generator-outputs');
        $archivePath = "id-generator/batches/{$batch->id}/batch.zip";
        $absolutePath = Storage::disk($outputDisk)->path($archivePath);

        if (! is_dir(dirname($absolutePath))) {
            mkdir(dirname($absolutePath), 0777, true);
        }

        $zip = new ZipArchive;
        $zip->open($absolutePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($batch->assets as $asset) {
            foreach (array_filter([
                'front.png' => $asset->front_png_path,
                'back.png' => $asset->back_png_path,
                'card.pdf' => $asset->pdf_path,
            ]) as $fileName => $path) {
                $zip->addFile(Storage::disk($outputDisk)->path($path), sprintf('%s/%s', $asset->source_id, $fileName));
            }
        }

        $zip->close();

        $batch->update([
            'archive_disk' => $outputDisk,
            'archive_path' => $archivePath,
        ]);

        return $archivePath;
    }
}

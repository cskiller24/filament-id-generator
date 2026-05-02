<?php

namespace Cskiller\FilamentIdGenerator\Actions;

use Barryvdh\DomPDF\Facade\Pdf;
use Cskiller\FilamentIdGenerator\Models\IdTemplate;
use Illuminate\Support\Facades\Storage;

class BuildGeneratedPdfAction
{
    public function handle(IdTemplate $template, array $pngPaths, string $outputPath): string
    {
        $outputDisk = (string) config('filament-id-generator.output_disk', 'filament-id-generator-outputs');

        $pages = collect($pngPaths)
            ->filter()
            ->map(fn (string $path): array => [
                'data_uri' => 'data:image/png;base64,' . base64_encode(Storage::disk($outputDisk)->get($path)),
            ])
            ->values()
            ->all();

        $pdf = Pdf::loadView('filament-id-generator::pdf.generated-id', [
            'pages' => $pages,
            'widthMm' => $template->width_mm,
            'heightMm' => $template->height_mm,
        ])->setPaper([0, 0, $template->width_mm * 2.83465, $template->height_mm * 2.83465]);

        Storage::disk($outputDisk)->put($outputPath, $pdf->output());

        return $outputPath;
    }
}

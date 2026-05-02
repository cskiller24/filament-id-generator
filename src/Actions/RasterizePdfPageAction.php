<?php

namespace Cskiller\FilamentIdGenerator\Actions;

use Spatie\PdfToImage\Enums\OutputFormat;
use Spatie\PdfToImage\Pdf;

class RasterizePdfPageAction
{
    public function handle(string $absolutePdfPath, int $pageNumber, string $absoluteOutputPath, int $resolution): string
    {
        $pdf = new Pdf($absolutePdfPath);

        $pdf
            ->selectPage($pageNumber)
            ->resolution($resolution)
            ->format(OutputFormat::Png)
            ->backgroundColor('#ffffff')
            ->save($absoluteOutputPath);

        return $absoluteOutputPath;
    }

    public function pageCount(string $absolutePdfPath): int
    {
        return (new Pdf($absolutePdfPath))->pageCount();
    }
}

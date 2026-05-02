<?php

namespace Cskiller\FilamentIdGenerator\Jobs;

use Cskiller\FilamentIdGenerator\Actions\BuildBatchArchiveAction;
use Cskiller\FilamentIdGenerator\Enums\IdGenerationStatus;
use Cskiller\FilamentIdGenerator\Models\IdGenerationBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FinalizeIdGenerationBatchJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $batchId) {}

    public function handle(BuildBatchArchiveAction $buildBatchArchive): void
    {
        $batch = IdGenerationBatch::with('assets')->findOrFail($this->batchId);

        $buildBatchArchive->handle($batch);

        $batch->update([
            'status' => $batch->failed_count > 0 ? IdGenerationStatus::Failed : IdGenerationStatus::Completed,
        ]);
    }
}

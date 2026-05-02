<?php

namespace Cskiller\FilamentIdGenerator\Jobs;

use Cskiller\FilamentIdGenerator\Actions\RenderGeneratedIdAction;
use Cskiller\FilamentIdGenerator\Enums\IdGenerationStatus;
use Cskiller\FilamentIdGenerator\Models\GeneratedIdAsset;
use Cskiller\FilamentIdGenerator\Models\IdGenerationBatch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class RenderGeneratedIdJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $idGenerationBatchId,
        public string $sourceType,
        public int $sourceId,
    ) {}

    public function handle(RenderGeneratedIdAction $renderGeneratedId): void
    {
        $batch = IdGenerationBatch::findOrFail($this->idGenerationBatchId);
        $recordClass = Relation::getMorphedModel($this->sourceType) ?? $this->sourceType;
        $record = $recordClass::query()->findOrFail($this->sourceId);

        if ($batch->pending_count > 0) {
            $batch->decrement('pending_count');
        }

        $batch->increment('processing_count');

        $renderGeneratedId->handle($batch, $record);

        if ($batch->processing_count > 0) {
            $batch->decrement('processing_count');
        }

        $batch->increment('completed_count');
    }

    public function failed(Throwable $exception): void
    {
        $batch = IdGenerationBatch::find($this->idGenerationBatchId);

        if (! $batch) {
            return;
        }

        GeneratedIdAsset::updateOrCreate(
            [
                'id_generation_batch_id' => $batch->id,
                'source_type' => $this->sourceType,
                'source_id' => $this->sourceId,
            ],
            [
                'status' => IdGenerationStatus::Failed,
                'error_message' => $exception->getMessage(),
            ]
        );

        if ($batch->processing_count > 0) {
            $batch->decrement('processing_count');
        }

        if ($batch->pending_count > 0) {
            $batch->decrement('pending_count');
        }

        $batch->increment('failed_count');
        $batch->update(['status' => IdGenerationStatus::Failed]);
    }
}

<?php

namespace Cskiller\FilamentIdGenerator\Actions;

use Cskiller\FilamentIdGenerator\Enums\IdGenerationStatus;
use Cskiller\FilamentIdGenerator\Jobs\FinalizeIdGenerationBatchJob;
use Cskiller\FilamentIdGenerator\Jobs\RenderGeneratedIdJob;
use Cskiller\FilamentIdGenerator\Models\IdGenerationBatch;
use Cskiller\FilamentIdGenerator\Models\IdTemplate;
use Cskiller\FilamentIdGenerator\Support\IdDataSourceRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Bus;

class StartIdGenerationBatchAction
{
    public function __construct(private IdDataSourceRegistry $registry) {}

    public function handle(IdTemplate $template, array $recordIds, Model $operator): IdGenerationBatch
    {
        $batch = IdGenerationBatch::create([
            'id_template_id' => $template->id,
            'initiated_by' => $operator->getKey(),
            'status' => IdGenerationStatus::Pending,
            'total_count' => count($recordIds),
            'pending_count' => count($recordIds),
        ]);

        $adapter = $this->registry->for($template->target_type);
        $jobs = $adapter->query()->whereKey($recordIds)->get()->map(
            fn ($record) => new RenderGeneratedIdJob($batch->id, $record->getMorphClass(), (int) $record->getKey())
        )->all();

        $laravelBatch = Bus::batch($jobs)
            ->name("id-generation-{$batch->id}")
            ->finally(fn () => FinalizeIdGenerationBatchJob::dispatch($batch->id))
            ->dispatch();

        $batch->update([
            'laravel_batch_id' => $laravelBatch->id,
            'status' => IdGenerationStatus::Processing,
        ]);

        return $batch->fresh();
    }
}

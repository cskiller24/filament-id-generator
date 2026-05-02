<?php

namespace Cskiller\FilamentIdGenerator\Database\Factories;

use Cskiller\FilamentIdGenerator\Enums\IdGenerationStatus;
use Cskiller\FilamentIdGenerator\Models\IdGenerationBatch;
use Cskiller\FilamentIdGenerator\Models\IdTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class IdGenerationBatchFactory extends Factory
{
    protected $model = IdGenerationBatch::class;

    public function definition(): array
    {
        $initiatedByModel = config('filament-id-generator.initiated_by_model', 'App\\Models\\User');

        return [
            'id_template_id' => IdTemplate::factory(),
            'initiated_by' => $initiatedByModel::factory(),
            'status' => IdGenerationStatus::Pending,
            'total_count' => 0,
            'pending_count' => 0,
            'processing_count' => 0,
            'completed_count' => 0,
            'failed_count' => 0,
            'laravel_batch_id' => null,
            'archive_disk' => null,
            'archive_path' => null,
            'failure_summary' => null,
        ];
    }
}

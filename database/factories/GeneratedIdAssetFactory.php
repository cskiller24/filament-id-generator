<?php

namespace Cskiller\FilamentIdGenerator\Database\Factories;

use Cskiller\FilamentIdGenerator\Enums\IdGenerationStatus;
use Cskiller\FilamentIdGenerator\Models\GeneratedIdAsset;
use Cskiller\FilamentIdGenerator\Models\IdGenerationBatch;
use Cskiller\FilamentIdGenerator\Tests\Fixtures\TestIdSourceRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

class GeneratedIdAssetFactory extends Factory
{
    protected $model = GeneratedIdAsset::class;

    public function definition(): array
    {
        return [
            'id_generation_batch_id' => IdGenerationBatch::factory(),
            'source_type' => (new TestIdSourceRecord)->getMorphClass(),
            'source_id' => TestIdSourceRecord::factory(),
            'status' => IdGenerationStatus::Completed,
            'front_png_disk' => 'local',
            'front_png_path' => null,
            'back_png_disk' => 'local',
            'back_png_path' => null,
            'pdf_disk' => 'local',
            'pdf_path' => null,
            'error_message' => null,
        ];
    }
}

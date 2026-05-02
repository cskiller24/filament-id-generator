<?php

namespace Cskiller\FilamentIdGenerator\Models;

use Cskiller\FilamentIdGenerator\Database\Factories\IdGenerationBatchFactory;
use Cskiller\FilamentIdGenerator\Enums\IdGenerationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IdGenerationBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_template_id',
        'initiated_by',
        'status',
        'total_count',
        'pending_count',
        'processing_count',
        'completed_count',
        'failed_count',
        'laravel_batch_id',
        'archive_disk',
        'archive_path',
        'failure_summary',
    ];

    protected $casts = [
        'status' => IdGenerationStatus::class,
    ];

    protected static function newFactory(): IdGenerationBatchFactory
    {
        return IdGenerationBatchFactory::new();
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(IdTemplate::class, 'id_template_id');
    }

    /** @deprecated Use template() instead */
    public function idTemplate(): BelongsTo
    {
        return $this->template();
    }

    public function initiatedBy(): BelongsTo
    {
        $modelClass = config('filament-id-generator.initiated_by_model');

        return $this->belongsTo($modelClass, 'initiated_by');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(GeneratedIdAsset::class);
    }
}

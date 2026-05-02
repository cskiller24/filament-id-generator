<?php

namespace Cskiller\FilamentIdGenerator\Models;

use Cskiller\FilamentIdGenerator\Database\Factories\GeneratedIdAssetFactory;
use Cskiller\FilamentIdGenerator\Enums\IdGenerationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GeneratedIdAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_generation_batch_id',
        'source_type',
        'source_id',
        'status',
        'front_png_disk',
        'front_png_path',
        'back_png_disk',
        'back_png_path',
        'pdf_disk',
        'pdf_path',
        'error_message',
    ];

    protected $casts = [
        'status' => IdGenerationStatus::class,
    ];

    protected static function newFactory(): GeneratedIdAssetFactory
    {
        return GeneratedIdAssetFactory::new();
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(IdGenerationBatch::class, 'id_generation_batch_id');
    }

    /** @deprecated Use batch() instead */
    public function idGenerationBatch(): BelongsTo
    {
        return $this->batch();
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }
}

<?php

namespace Cskiller\FilamentIdGenerator\Models;

use Cskiller\FilamentIdGenerator\Database\Factories\IdTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IdTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'target_type',
        'width_mm',
        'height_mm',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    protected static function newFactory(): IdTemplateFactory
    {
        return IdTemplateFactory::new();
    }

    public function sides(): HasMany
    {
        return $this->hasMany(IdTemplateSide::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(IdGenerationBatch::class);
    }
}

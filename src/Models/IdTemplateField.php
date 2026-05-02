<?php

namespace Cskiller\FilamentIdGenerator\Models;

use Cskiller\FilamentIdGenerator\Database\Factories\IdTemplateFieldFactory;
use Cskiller\FilamentIdGenerator\Enums\IdTemplateFieldType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdTemplateField extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_template_side_id',
        'label',
        'type',
        'mapping_key',
        'x',
        'y',
        'width',
        'height',
        'z_index',
        'is_required',
        'default_value',
        'properties',
    ];

    protected $casts = [
        'type' => IdTemplateFieldType::class,
        'is_required' => 'boolean',
        'properties' => 'array',
    ];

    protected static function newFactory(): IdTemplateFieldFactory
    {
        return IdTemplateFieldFactory::new();
    }

    public function side(): BelongsTo
    {
        return $this->belongsTo(IdTemplateSide::class, 'id_template_side_id');
    }
}

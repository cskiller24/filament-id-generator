<?php

namespace Cskiller\FilamentIdGenerator\Models;

use Cskiller\FilamentIdGenerator\Database\Factories\IdTemplateSideFactory;
use Cskiller\FilamentIdGenerator\Enums\IdTemplateSideType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IdTemplateSide extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_template_id',
        'side',
        'source_disk',
        'source_path',
        'source_mime_type',
        'preview_disk',
        'preview_path',
        'page_number',
        'canvas_width',
        'canvas_height',
    ];

    protected $casts = [
        'side' => IdTemplateSideType::class,
    ];

    protected static function newFactory(): IdTemplateSideFactory
    {
        return IdTemplateSideFactory::new();
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(IdTemplate::class, 'id_template_id');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(IdTemplateField::class)->orderBy('z_index');
    }
}

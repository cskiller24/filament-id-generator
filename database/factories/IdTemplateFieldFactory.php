<?php

namespace Cskiller\FilamentIdGenerator\Database\Factories;

use Cskiller\FilamentIdGenerator\Enums\IdTemplateFieldType;
use Cskiller\FilamentIdGenerator\Models\IdTemplateField;
use Cskiller\FilamentIdGenerator\Models\IdTemplateSide;
use Illuminate\Database\Eloquent\Factories\Factory;

class IdTemplateFieldFactory extends Factory
{
    protected $model = IdTemplateField::class;

    public function definition(): array
    {
        return [
            'id_template_side_id' => IdTemplateSide::factory(),
            'label' => 'Full name',
            'type' => IdTemplateFieldType::Text,
            'mapping_key' => 'full_name',
            'x' => 24,
            'y' => 24,
            'width' => 120,
            'height' => 20,
            'z_index' => 0,
            'is_required' => false,
            'default_value' => null,
            'properties' => null,
        ];
    }
}

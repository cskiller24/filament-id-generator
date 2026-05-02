<?php

namespace Cskiller\FilamentIdGenerator\Database\Factories;

use Cskiller\FilamentIdGenerator\Enums\IdTemplateSideType;
use Cskiller\FilamentIdGenerator\Models\IdTemplate;
use Cskiller\FilamentIdGenerator\Models\IdTemplateSide;
use Illuminate\Database\Eloquent\Factories\Factory;

class IdTemplateSideFactory extends Factory
{
    protected $model = IdTemplateSide::class;

    public function definition(): array
    {
        return [
            'id_template_id' => IdTemplate::factory(),
            'side' => IdTemplateSideType::Front,
            'source_disk' => 'local',
            'source_path' => 'id-generator/templates/1/sources/front.png',
            'source_mime_type' => 'image/png',
            'preview_disk' => 'public',
            'preview_path' => 'id-generator/templates/1/previews/front.png',
            'page_number' => null,
            'canvas_width' => 320,
            'canvas_height' => 200,
        ];
    }

    public function front(): static
    {
        return $this->state(['side' => IdTemplateSideType::Front]);
    }

    public function back(): static
    {
        return $this->state(['side' => IdTemplateSideType::Back]);
    }
}

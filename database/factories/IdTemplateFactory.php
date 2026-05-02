<?php

namespace Cskiller\FilamentIdGenerator\Database\Factories;

use Cskiller\FilamentIdGenerator\Models\IdTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class IdTemplateFactory extends Factory
{
    protected $model = IdTemplate::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true) . ' ID',
            'target_type' => 'user',
            'width_mm' => 85.60,
            'height_mm' => 53.98,
            'is_active' => true,
            'settings' => null,
        ];
    }
}

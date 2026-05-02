<?php

namespace Cskiller\FilamentIdGenerator\Tests\Fixtures;

use Illuminate\Database\Eloquent\Factories\Factory;

class TestIdSourceRecordFactory extends Factory
{
    protected $model = TestIdSourceRecord::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'avatar_path' => null,
        ];
    }
}

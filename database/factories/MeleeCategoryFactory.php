<?php

namespace Database\Factories;

use App\Models\MeleeCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MeleeCategory>
 */
class MeleeCategoryFactory extends Factory
{
    protected $model = MeleeCategory::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'name'            => ucwords($name),
            'slug'            => Str::slug($name),
            'parent_id'       => null,
            'type'            => $this->faker->randomElement(['lab_grown', 'natural']),
            'cut_type'        => $this->faker->randomElement(['brilliant', 'rose', 'salt_pepper', 'round_rose', 'round_brilliant', 'tambuli']),
            'allowed_shapes'  => ['Round', 'Pear'],
            'has_color_layer' => false,
            'sort_order'      => $this->faker->numberBetween(0, 10),
            'is_active'       => true,
        ];
    }

    public function labGrown(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'lab_grown',
        ]);
    }

    public function natural(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'natural',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }
}

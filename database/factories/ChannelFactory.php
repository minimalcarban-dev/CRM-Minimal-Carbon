<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\Channel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Channel>
 */
class ChannelFactory extends Factory
{
    protected $model = Channel::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'type' => 'group',
            'description' => $this->faker->sentence(),
            'created_by' => Admin::factory(),
        ];
    }
}

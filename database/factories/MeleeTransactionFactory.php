<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\MeleeDiamond;
use App\Models\MeleeTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MeleeTransaction>
 */
class MeleeTransactionFactory extends Factory
{
    protected $model = MeleeTransaction::class;

    public function definition(): array
    {
        return [
            'melee_diamond_id' => MeleeDiamond::factory(),
            'transaction_type' => 'in',
            'pieces'           => $this->faker->numberBetween(5, 50),
            'carat_weight'     => round($this->faker->randomFloat(3, 0.1, 5.0), 3),
            'reference_type'   => 'manual',
            'reference_id'     => null,
            'notes'            => $this->faker->optional()->sentence(),
            'created_by'       => Admin::factory(),
        ];
    }

    /**
     * State: Stock IN transaction (purchase/return).
     */
    public function stockIn(): static
    {
        return $this->state(fn(array $attributes) => [
            'transaction_type' => 'in',
            'reference_type'   => 'manual',
        ]);
    }

    /**
     * State: Stock OUT transaction (sale/usage).
     */
    public function stockOut(): static
    {
        return $this->state(fn(array $attributes) => [
            'transaction_type' => 'out',
            'reference_type'   => 'manual',
        ]);
    }

    /**
     * State: Adjustment transaction.
     */
    public function adjustment(): static
    {
        return $this->state(fn(array $attributes) => [
            'transaction_type' => 'adjustment',
            'reference_type'   => 'manual',
        ]);
    }

    /**
     * State: Transaction linked to a specific order.
     */
    public function forOrder(int $orderId): static
    {
        return $this->state(fn(array $attributes) => [
            'reference_type' => 'order',
            'reference_id'   => $orderId,
        ]);
    }

    /**
     * Attach to a specific diamond.
     */
    public function forDiamond(MeleeDiamond $diamond): static
    {
        return $this->state(fn(array $attributes) => [
            'melee_diamond_id' => $diamond->id,
        ]);
    }

    /**
     * Attach to a specific admin as creator.
     */
    public function createdByAdmin(Admin $admin): static
    {
        return $this->state(fn(array $attributes) => [
            'created_by' => $admin->id,
        ]);
    }
}

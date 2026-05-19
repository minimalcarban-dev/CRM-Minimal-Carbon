<?php

namespace Database\Factories;

use App\Models\MeleeCategory;
use App\Models\MeleeDiamond;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MeleeDiamond>
 */
class MeleeDiamondFactory extends Factory
{
    protected $model = MeleeDiamond::class;

    public function definition(): array
    {
        $shape = $this->faker->randomElement(['Round', 'Pear', 'Marquise', 'Oval']);
        $size = $this->faker->randomFloat(1, 0.5, 3.0);
        $totalPieces = $this->faker->numberBetween(50, 500);
        $availablePieces = $this->faker->numberBetween(10, $totalPieces);
        $totalCarat = round($totalPieces * $this->faker->randomFloat(3, 0.005, 0.05), 3);
        $availableCarat = round($availablePieces * $this->faker->randomFloat(3, 0.005, 0.05), 3);
        $purchasePrice = $this->faker->randomFloat(2, 10, 200);

        return [
            'melee_category_id'      => MeleeCategory::factory(),
            'shape'                  => $shape,
            'color'                  => null,
            'sieve_size'             => null,
            'size_label'             => strtolower($shape) . '-' . $size,
            'total_pieces'           => $totalPieces,
            'available_pieces'       => $availablePieces,
            'sold_pieces'            => $totalPieces - $availablePieces,
            'total_carat_weight'     => $totalCarat,
            'available_carat_weight' => $availableCarat,
            'purchase_price_per_ct'  => $purchasePrice,
            'listing_price_per_ct'   => round($purchasePrice * 1.3, 2),
            'total_price'            => round($availableCarat * $purchasePrice, 2),
            'status'                 => $availablePieces > 50 ? 'in_stock' : ($availablePieces > 0 ? 'low_stock' : 'out_of_stock'),
            'low_stock_threshold'    => 10,
        ];
    }

    /**
     * State: Diamond is in stock with healthy availability.
     */
    public function inStock(): static
    {
        return $this->state(fn(array $attributes) => [
            'total_pieces'     => 200,
            'available_pieces' => 150,
            'sold_pieces'      => 50,
            'status'           => 'in_stock',
        ]);
    }

    /**
     * State: Diamond is completely out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn(array $attributes) => [
            'total_pieces'           => 100,
            'available_pieces'       => 0,
            'sold_pieces'            => 100,
            'available_carat_weight' => 0,
            'total_price'            => 0,
            'status'                 => 'out_of_stock',
        ]);
    }

    /**
     * State: Diamond is at low stock threshold.
     */
    public function lowStock(): static
    {
        return $this->state(fn(array $attributes) => [
            'total_pieces'     => 100,
            'available_pieces' => 5,
            'sold_pieces'      => 95,
            'status'           => 'low_stock',
        ]);
    }

    /**
     * State: Diamond has negative stock (known data integrity issue).
     */
    public function negativeStock(): static
    {
        return $this->state(fn(array $attributes) => [
            'total_pieces'     => 50,
            'available_pieces' => -5,
            'sold_pieces'      => 55,
            'status'           => 'out_of_stock',
        ]);
    }

    /**
     * Attach to a specific category.
     */
    public function forCategory(MeleeCategory $category): static
    {
        return $this->state(fn(array $attributes) => [
            'melee_category_id' => $category->id,
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MeleeCategory;

class MeleeCategoriesSeeder extends Seeder
{
    public function run()
    {
        // ── Standard shapes available for all categories ──
        $standardShapes = ['Round', 'Pear', 'Oval', 'Marquise', 'Baguette'];

        // ─────────────────────────────────────────────────
        // 1. Lab Grown Diamonds (3 categories)
        // ─────────────────────────────────────────────────
        $labGrown = [
            [
                'name' => 'Brilliant Cut Diamond',
                'slug' => 'lab-brilliant',
                'type' => 'lab_grown',
                'cut_type' => 'brilliant',
                'allowed_shapes' => $standardShapes,
                'sort_order' => 10,
            ],
            [
                'name' => 'Rose Cut Diamond',
                'slug' => 'lab-rose',
                'type' => 'lab_grown',
                'cut_type' => 'rose',
                'allowed_shapes' => $standardShapes,
                'sort_order' => 20,
            ],
            [
                'name' => 'Salt and Pepper',
                'slug' => 'lab-salt-pepper',
                'type' => 'lab_grown',
                'cut_type' => 'salt_pepper',
                'allowed_shapes' => $standardShapes,
                'sort_order' => 25,
            ],
        ];

        foreach ($labGrown as $data) {
            MeleeCategory::updateOrCreate(['slug' => $data['slug']], $data);
        }

        // ─────────────────────────────────────────────────
        // 2. Natural Diamonds
        // ─────────────────────────────────────────────────
        $natural = [
            [
                'name' => 'Salt & Pepper',
                'slug' => 'natural-salt-pepper',
                'type' => 'natural',
                'cut_type' => 'salt_pepper',
                'allowed_shapes' => $standardShapes,
                'sort_order' => 30,
            ],
            [
                'name' => 'Rose Cut',
                'slug' => 'natural-round-rose',
                'type' => 'natural',
                'cut_type' => 'round_rose',
                'allowed_shapes' => $standardShapes,
                'sort_order' => 40,
            ],
            [
                'name' => 'Brilliant Cut',
                'slug' => 'natural-round-brilliant',
                'type' => 'natural',
                'cut_type' => 'round_brilliant',
                'allowed_shapes' => $standardShapes,
                'sort_order' => 50,
            ],
            [
                'name' => 'Tambuli Cut',
                'slug' => 'natural-tambuli',
                'type' => 'natural',
                'cut_type' => 'tambuli',
                'has_color_layer' => true,
                'allowed_shapes' => $standardShapes,
                'sort_order' => 60,
            ],
        ];

        foreach ($natural as $data) {
            MeleeCategory::updateOrCreate(['slug' => $data['slug']], $data);
        }
    }
}

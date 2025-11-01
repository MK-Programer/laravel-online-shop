<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);
        $trackQty = fake()->randomElement(['Yes', 'No']);
        $qty = $trackQty == 'Yes' ? fake()->numberBetween(0, 100) : null;
        return [
            'title' => $title,
            'slug' => str()->slug($title),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 10, 500),
            'compare_price' => fake()->optional()->randomFloat(2, 10, 700),
            'category_id' => fake()->numberBetween(1, 10),
            'sub_category_id' => fake()->optional()->numberBetween(1, 20),
            'brand_id' => fake()->optional()->numberBetween(1, 10),
            'is_featured' => fake()->randomElement(['Yes', 'No']),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####??')),
            'bar_code' => fake()->optional(0.8)->ean13(), // 13-digit barcode
            'track_qty' => $trackQty,
            'qty' => $qty,
            'status' => fake()->boolean(),
        ];
    }
}

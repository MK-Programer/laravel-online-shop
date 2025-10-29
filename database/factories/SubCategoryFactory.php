<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubCategory>
 */
class SubCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categoriesIds = Category::pluck('id')->toArray();
        $name = fake()->unique()->name();
        $slug = str()->slug($name);
        return [
            'category_id' => $categoriesIds[array_rand($categoriesIds)],
            'name' => $name,
            'slug' => $slug,
            'status' => fake()->boolean(),
        ];
    }
}

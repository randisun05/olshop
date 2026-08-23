<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
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
        $name = fake()->unique()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'weight' => fake()->numberBetween(100, 2000),
            'is_active' => true,
            'is_featured' => false,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Product $product) {
            if ($product->variants()->doesntExist()) {
                $product->variants()->create([
                    'sku' => Str::upper(Str::random(10)),
                    'price' => fake()->numberBetween(10000, 500000),
                    'stock' => fake()->numberBetween(0, 50),
                ]);
            }
        });
    }
}

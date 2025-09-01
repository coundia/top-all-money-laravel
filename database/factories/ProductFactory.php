<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'code' => strtoupper(Str::random(6)),
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'barcode' => $this->faker->ean13(),
            'unitId' => $this->faker->uuid(),
            'categoryId' => $this->faker->uuid(),
            'defaultPrice' => $this->faker->numberBetween(100, 10000),
            'purchasePrice' => $this->faker->numberBetween(50, 5000),
            'statuses' => $this->faker->randomElement(['ACTIVE','INACTIVE']),
            'remoteId' => null,
            'localId' => null,
            'createdBy' => $this->faker->uuid(),
            'version' => 0,
            'isDirty' => true,
            'createdAt' => now(),
            'updatedAt' => now(),
        ];
    }
}

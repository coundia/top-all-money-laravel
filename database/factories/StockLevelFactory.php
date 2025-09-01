<?php

namespace Database\Factories;

use App\Models\StockLevel;
use App\Models\Product;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StockLevelFactory extends Factory
{
    protected $model = StockLevel::class;

    public function definition(): array
    {
        $product = Product::factory()->create();
        $company = Company::factory()->create();

        return [
            'id'              => (string) Str::uuid(),
            'code'            => 'SL-'.$this->faker->numberBetween(1000, 9999),
            'remoteId'        => null,
            'localId'         => null,
            'stockOnHand'     => $this->faker->numberBetween(0, 250),
            'stockAllocated'  => $this->faker->numberBetween(0, 100),
            'productVariantId'=> $product->id,
            'companyId'       => $company->id,
            'syncAt'          => null,
            'version'         => 0,
            'account'         => 'ACC-1',
            'isDirty'         => false,
            'createdBy'       => (string) Str::uuid(),
            'createdAt'       => now(),
            'updatedAt'       => now(),
            'deletedAt'       => null,
        ];
    }
}

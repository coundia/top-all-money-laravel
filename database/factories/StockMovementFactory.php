<?php

namespace Database\Factories;

use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        $nowIso = now()->toISOString();

        return [
            'id'                 => (string) Str::uuid(),
            'type_stock_movement'=> $this->faker->randomElement(['IN','OUT','ADJUSTMENT']),
            'code'               => 'SM-' . $this->faker->unique()->numerify('####'),
            'remoteId'           => null,
            'localId'            => null,
            'quantity'           => $this->faker->numberBetween(1, 50),
            'companyId'          => (string) Str::uuid(),
            'productVariantId'   => (string) Str::uuid(),
            'orderLineId'        => null,
            'discriminator'      => null,
            'account'            => 'ACC-1',
            'syncAt'             => null,
            'version'            => 0,
            'isDirty'            => 0,
            'createdBy'          => null,
            'createdAt'          => $nowIso,
            'updatedAt'          => $nowIso,
        ];
    }
}

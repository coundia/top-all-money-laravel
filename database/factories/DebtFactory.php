<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Debt;

/**
 * Factory for generating fake Debt records for testing.
 */
class DebtFactory extends Factory
{
    protected $model = Debt::class;

    public function definition(): array
    {
        $now = now();

        return [
            'id'          => (string) Str::uuid(),
            'remoteId'    => null,
            'localId'     => null,
            'code'        => strtoupper($this->faker->bothify('DEBT-###')),
            'notes'       => $this->faker->sentence(4),
            'balance'     => $this->faker->numberBetween(0, 5000),
            'balanceDebt' => $this->faker->numberBetween(0, 2000),
             'statuses'    => $this->faker->randomElement(['PENDING','PAID','CANCELLED']),
            'account'     => null,
            'customerId'  => null,
            'createdAt'   => $now,
            'updatedAt'   => $now,
            'deletedAt'   => null,
            'syncAt'      => null,
            'createdBy'   => null,
            'version'     => 0,
            'isDirty'     => 1,
        ];
    }
}

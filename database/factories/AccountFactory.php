<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'remoteId' => null,
            'localId' => null,
            'code' => 'ACC-'.fake()->unique()->numerify('###'),
            'description' => fake()->sentence(),
            'currency' => fake()->randomElement(['XOF','USD','EUR']),
            'status' => fake()->randomElement(['ACTIVE','ARCHIVED',null]),
            'typeAccount' => fake()->randomElement(['CURRENT','SAVING',null]),
            'dateStartAccount' => null,
            'dateEndAccount' => null,
            'balance' => fake()->numberBetween(0, 1_000_000),
            'balance_prev' => 0,
            'balance_blocked' => 0,
            'balance_init' => 0,
            'balance_goal' => 0,
            'balance_limit' => 0,
            'isDefault' => 0,
            'isShared' => 0,
            'isDirty' => 1,
            'version' => 0,
            'createdBy' => null,
            'createdAt' => now()->toISOString(),
            'updatedAt' => now()->toISOString(),
            'deletedAt' => null,
            'syncAt' => null,
        ];
    }
}

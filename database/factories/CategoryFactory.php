<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Category;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $now = now()->toISOString();

        return [
            'id'          => (string) Str::uuid(),
            'remoteId'    => null,
            'localId'     => null,
            'code'        => strtoupper($this->faker->bothify('CAT-###')),
            'description' => $this->faker->sentence(3),
            'typeEntry'   => $this->faker->randomElement(['DEBIT','CREDIT']),
            'account'     => null,
            'createdAt'   => $now,
            'updatedAt'   => $now,
            'deletedAt'   => null,
            'syncAt'      => null,
            'isShared'    => 0,
            'createdBy'   => null,
            'version'     => 0,
            'isDirty'     => 1,
        ];
    }
}

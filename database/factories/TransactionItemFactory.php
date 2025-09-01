<?php

namespace Database\Factories;

use App\Models\TransactionItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TransactionItemFactory extends Factory
{
    protected $model = TransactionItem::class;

    public function definition(): array
    {
        $now       = now();
        $quantity  = $this->faker->numberBetween(1, 10);
        $unitPrice = $this->faker->numberBetween(100, 10_000);

        return [
            'id'             => (string) Str::uuid(),
            'transactionId'  => (string) Str::uuid(),
            'productId'      => (string) Str::uuid(),
            'remoteId'       => null,
            'localId'        => null,
            'label'          => $this->faker->words(3, true),
            'quantity'       => $quantity,
            'unitId'         => (string) Str::uuid(),
            'unitPrice'      => $unitPrice,
            'total'          => $quantity * $unitPrice,
            'notes'          => $this->faker->optional()->sentence(),

            // custom timestamps/flags (model has $timestamps = false)
            'createdAt'      => $now,
            'updatedAt'      => $now,
            'deletedAt'      => null,
            'account'        => 'ACC-1',
            'syncAt'         => null,
            'code'           => 'TI-'.$this->faker->numerify('####'),
            'createdBy'      => (string) Str::uuid(),
            'version'        => 0,
            'isDirty'        => false,
        ];
    }

    public function softDeleted(): self
    {
        return $this->state(fn () => ['deletedAt' => now()]);
    }

    public function dirty(): self
    {
        return $this->state(fn () => ['isDirty' => true]);
    }
}

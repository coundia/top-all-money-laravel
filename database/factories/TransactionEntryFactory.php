<?php

namespace Database\Factories;

use App\Models\TransactionEntry;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TransactionEntryFactory extends Factory
{
    protected $model = TransactionEntry::class;

    public function definition(): array
    {
        $now = now();

        return [
            'id'              => (string) Str::uuid(),
            'remoteId'        => null,
            'localId'         => null,
            'code'            => 'TXN-'.$this->faker->numerify('####'),
            'description'     => $this->faker->sentence(),
            'amount'          => $this->faker->numberBetween(1, 10000),
            'typeEntry'       => $this->faker->randomElement(['DEBIT', 'CREDIT']),
            'dateTransaction' => $this->faker->date('Y-m-d'),
            'status'          => $this->faker->randomElement(['PENDING', 'POSTED', 'CANCELLED']),
            'entityName'      => $this->faker->randomElement(['invoice', 'payment', 'adjustment']),
            'entityId'        => (string) Str::uuid(),
            'accountId'       => (string) Str::uuid(),
            'categoryId'      => (string) Str::uuid(),
            'companyId'       => (string) Str::uuid(),
            'customerId'      => (string) Str::uuid(),
            'debtId'          => $this->faker->boolean(30) ? (string) Str::uuid() : null,

            // camelCase timestamps & flags (you disabled $timestamps)
            'createdAt'       => $now,
            'updatedAt'       => $now,
            'deletedAt'       => null,
            'syncAt'          => null,
            'version'         => 0,
            'createdBy'       => (string) Str::uuid(),
            'isDirty'         => false,
        ];
    }

    public function debit(): self
    {
        return $this->state(fn () => ['typeEntry' => 'DEBIT']);
    }

    public function credit(): self
    {
        return $this->state(fn () => ['typeEntry' => 'CREDIT']);
    }

    public function posted(): self
    {
        return $this->state(fn () => ['status' => 'POSTED']);
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

<?php

namespace Database\Factories;

use App\Models\AccountUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AccountUserFactory extends Factory
{
    protected $model = AccountUser::class;

    public function definition(): array
    {
        $nowIso = now()->toISOString();

        return [
            'id'        => (string) Str::uuid(),
            'code'      => 'AU-' . $this->faker->unique()->numerify('####'),
            'account'   => 'ACC-' . $this->faker->randomDigitNotNull(),
            'user'      => 'U-' . $this->faker->randomDigitNotNull(),
            'email'     => $this->faker->unique()->safeEmail(),
            'phone'     => $this->faker->e164PhoneNumber(),
            'identify'  => $this->faker->swiftBicNumber(),
            'role'      => $this->faker->randomElement(['OWNER','ADMIN','VIEWER']),
            'status'    => $this->faker->randomElement(['INVITED','ACTIVE','REVOKED']),
            'invitedBy' => 'U-' . $this->faker->randomDigitNotNull(),
            // ⚠️ NOT NULL en DB → toujours renseigner
            'invitedAt' => $nowIso,
            'acceptedAt'=> null,
            'revokedAt' => null,
            'createdAt' => $nowIso,
            'updatedAt' => $nowIso,
            'deletedAt' => null,
            'syncAt'    => null,
            'version'   => 0,
            'isDirty'   => 0,
            'remoteId'  => null,
            'createdBy' => null,
            'localId'   => null,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Conversation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    public function definition(): array
    {
        return [
            'id'         => (string) Str::uuid(),
            'title'      => $this->faker->sentence(3),
            'status'     => $this->faker->randomElement(['OPEN','CLOSED']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

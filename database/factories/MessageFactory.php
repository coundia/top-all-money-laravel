<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'id'              => (string) Str::uuid(),
            'conversation_id' => Conversation::factory(),
            'sender'          => $this->faker->randomElement(['system','user','agent']),
            'content'         => $this->faker->paragraph(),
            'status'          => $this->faker->randomElement(['SENT','RECEIVED','READ']),
            'created_at'      => now(),
            'updated_at'      => now(),
        ];
    }
}

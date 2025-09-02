<?php

namespace Tests\Feature\Api;

use Tests\ApiTestCase;
use Tests\TestCase;
use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Feature tests for Message API
 */
class MessageApiTest extends ApiTestCase
{

    public function test_index_returns_paginated_messages(): void
    {
        Message::factory()->count(3)->create();

        $res = $this->getJson('/api/messages?per_page=2');

        $res->assertOk()
            ->assertJsonStructure(['data','links','meta'])
            ->assertJsonPath('meta.per_page', 2);
    }

    public function test_store_creates_message(): void
    {
        $payload = [
            'conversation_id' => '123e4567-e89b-12d3-a456-426614174000',
            'content' => 'Hello world',
            'sender' => 'me'
        ];

        $res = $this->postJson('/api/messages', $payload);

        $res->assertStatus(201)
            ->assertJsonPath('data.content', 'Hello world');
        $this->assertDatabaseHas('messages', ['content' => 'Hello world']);
    }

    public function test_show_returns_message(): void
    {
        $msg = Message::factory()->create();
        $this->getJson("/api/messages/{$msg->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $msg->id);
    }

    public function test_update_modifies_message(): void
    {
        $msg = Message::factory()->create(['content' => 'Old']);

        $this->putJson("/api/messages/{$msg->id}", ['content' => 'New'])
            ->assertOk()
            ->assertJsonPath('data.content', 'New');

        $this->assertDatabaseHas('messages', ['id' => $msg->id, 'content' => 'New']);
    }

    public function test_destroy_deletes_message(): void
    {
        $msg = Message::factory()->create();

        $this->deleteJson("/api/messages/{$msg->id}")
            ->assertOk();

        $this->assertDatabaseMissing('messages', ['id' => $msg->id]);
    }
}

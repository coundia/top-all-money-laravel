<?php

namespace Tests\Feature\Api;

use Tests\ApiTestCase;
use Tests\TestCase;
use App\Models\Conversation;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Feature tests for Conversation API endpoints.
 */
class ConversationApiTest extends ApiTestCase
{

    public function test_index_returns_paginated_conversations(): void
    {
        Conversation::factory()->count(3)->create();

        $res = $this->getJson('/api/conversations?per_page=2');

        $res->assertOk()
            ->assertJsonStructure(['data','links','meta'])
            ->assertJsonPath('meta.per_page', 2);
    }

    public function test_store_creates_conversation(): void
    {
        $payload = ['title' => 'Hello'];

        $res = $this->postJson('/api/conversations', $payload);

        $res->assertStatus(201)
            ->assertJsonPath('data.title', 'Hello');

        $this->assertDatabaseHas('conversations', ['title' => 'Hello']);
    }

    public function test_update_modifies_conversation(): void
    {
        $row = Conversation::factory()->create(['title' => 'Old']);

        $this->putJson("/api/conversations/{$row->id}", ['title' => 'New'])
            ->assertOk()
            ->assertJsonPath('data.title', 'New');

        $this->assertDatabaseHas('conversations', ['id' => $row->id, 'title' => 'New']);
    }

    public function test_destroy_deletes_hard(): void
    {
        $row = Conversation::factory()->create();

        $this->deleteJson("/api/conversations/{$row->id}")
            ->assertOk();

        $this->assertDatabaseMissing('conversations', ['id' => $row->id]);
    }

    public function test_bulk_upsert_create_update_delete(): void
    {
        $existing = Conversation::factory()->create(['title' => 'Before']);
        $toDelete = Conversation::factory()->create(['title' => 'Remove me']);

        $payload = [
            'items' => [
                ['type' => 'UPDATE', 'id' => $existing->id, 'title' => 'After'],
                ['type' => 'CREATE', 'title' => 'New-001'],
                ['type' => 'DELETE', 'id' => $toDelete->id],
            ],
        ];

        $res = $this->postJson('/api/conversations/bulk', $payload);
        $res->assertOk()->assertJsonCount(3, 'data');

        $this->assertDatabaseHas('conversations', ['id' => $existing->id, 'title' => 'After']);
        $this->assertDatabaseHas('conversations', ['title' => 'New-001']);
        $this->assertDatabaseMissing('conversations', ['id' => $toDelete->id]);
    }

    public function test_export_csv_downloads(): void
    {
        Conversation::factory()->count(2)->create();

        $res = $this->get('/api/conversations/export');

        $res->assertOk();
        $this->assertStringContainsString('text/csv', $res->headers->get('content-type'));
        $csv = $res->streamedContent();

        // Expect camel headers to match project convention
        $this->assertStringContainsString('id,title,createdBy,createdAt,updatedAt', $csv);
    }

    public function test_import_csv_upserts(): void
    {
        $csv = implode("\n", [
            'id,title,createdBy,createdAt,updatedAt',
            Str::uuid().',"Kickoff","alice","2025-01-01 10:00:00","2025-01-01 10:00:00"',
            ', "Standup",, , ',
        ]);

        $file = UploadedFile::fake()->createWithContent('conversations.csv', $csv);

        $res = $this->post('/api/conversations/import', ['file' => $file]);

        $res->assertOk()->assertJson(['status' => 'imported']);
        $this->assertDatabaseHas('conversations', ['title' => 'Kickoff']);
        $this->assertDatabaseHas('conversations', ['title' => 'Standup']);
    }
}

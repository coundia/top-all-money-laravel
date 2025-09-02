<?php
// This test suite covers CRUD, filtering, bulk, import/export for TransactionEntry.

namespace Tests\Feature\Api;

use Tests\ApiTestCase;
use Tests\TestCase;
use App\Models\TransactionEntry;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionEntryApiTest extends ApiTestCase
{

    public function test_index_returns_paginated(): void
    {
        TransactionEntry::factory()->count(3)->create(['deletedAt' => null]);
        $res = $this->getJson('/api/transaction-entries?per_page=2');

        $res->assertOk()
            ->assertJsonStructure(['data','links','meta'])
            ->assertJsonPath('meta.per_page', 2);
    }

    public function test_index_supports_filter_and_sort(): void
    {
        TransactionEntry::factory()->create(['code' => 'TE-AAA', 'description' => 'hello', 'isDirty' => 1, 'updatedAt' => now()->subDay()->toISOString()]);
        TransactionEntry::factory()->create(['code' => 'TE-ZZZ', 'description' => 'hello world', 'isDirty' => 0, 'updatedAt' => now()->toISOString()]);
        TransactionEntry::factory()->create(['code' => 'OTHER-123', 'description' => 'nope', 'isDirty' => 1]);

        $res = $this->getJson('/api/transaction-entries?q=hello&isDirty=1&sort=code:asc&per_page=50');

        $res->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'TE-AAA');
    }

    public function test_store_creates(): void
    {
        $payload = ['code' => 'TE-001', 'amount' => 1500, 'typeEntry' => 'DEBIT'];
        $res = $this->postJson('/api/transaction-entries', $payload);

        $res->assertStatus(201)
            ->assertJsonPath('data.code', 'TE-001')
            ->assertJsonPath('data.amount', 1500)
            ->assertJsonPath('data.typeEntry', 'DEBIT');

        $this->assertDatabaseHas('transaction_entry', ['code' => 'TE-001']);
    }

    public function test_store_validates(): void
    {
        $this->postJson('/api/transaction-entries', ['amount' => 'NaN'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['amount','typeEntry']);
    }

    public function test_show_returns_resource(): void
    {
        $e = TransactionEntry::factory()->create();
        $this->getJson("/api/transaction-entries/{$e->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $e->id);
    }

    public function test_update_modifies(): void
    {
        $e = TransactionEntry::factory()->create(['description' => 'Old']);
        $this->putJson("/api/transaction-entries/{$e->id}", ['description' => 'Updated'])
            ->assertOk()
            ->assertJsonPath('data.description', 'Updated');

        $this->assertDatabaseHas('transaction_entry', ['id' => $e->id, 'description' => 'Updated']);
    }

    public function test_destroy_soft_deletes(): void
    {
        $e = TransactionEntry::factory()->create();
        $this->deleteJson("/api/transaction-entries/{$e->id}")->assertOk();
        $this->assertNotNull(TransactionEntry::find($e->id)->deletedAt);
    }

    public function test_restore_undeletes(): void
    {
        $e = TransactionEntry::factory()->create(['deletedAt' => now()->toISOString()]);
        $this->postJson("/api/transaction-entries/{$e->id}/restore")
            ->assertOk()
            ->assertJsonPath('data.id', $e->id);
        $this->assertNull(TransactionEntry::find($e->id)->deletedAt);
    }

    public function test_bulk_upsert_creates_updates_deletes(): void
    {
        $existing = TransactionEntry::factory()->create(['code' => 'EXIST', 'amount' => 1, 'typeEntry' => 'DEBIT']);
        $toDelete = TransactionEntry::factory()->create(['code' => 'DELME', 'deletedAt' => null]);

        $payload = [
            'items' => [
                ['type' => 'UPDATE', 'id' => $existing->id, 'code' => 'EXIST-UP', 'amount' => 2, 'typeEntry' => 'CREDIT'],
                ['type' => 'CREATE', 'code' => 'NEW-001', 'amount' => 3, 'typeEntry' => 'DEBIT'],
                ['type' => 'DELETE', 'id' => $toDelete->id],
            ],
        ];

        $res = $this->postJson('/api/transaction-entries/bulk', $payload);
        $res->assertOk()->assertJsonCount(3, 'data');

        $this->assertDatabaseHas('transaction_entry', ['id' => $existing->id, 'code' => 'EXIST-UP', 'amount' => 2, 'typeEntry' => 'CREDIT']);
        $this->assertDatabaseHas('transaction_entry', ['code' => 'NEW-001', 'amount' => 3, 'typeEntry' => 'DEBIT']);
        $this->assertNotNull(TransactionEntry::find($toDelete->id)->deletedAt);
    }

    public function test_export_csv_downloads(): void
    {
        TransactionEntry::factory()->count(2)->create();
        $res = $this->get('/api/transaction-entries/export');

        $res->assertOk();
        $this->assertStringContainsString('text/csv', $res->headers->get('content-type'));
        $csv = $res->streamedContent();
        $this->assertStringContainsString('id,remoteId,localId,code,description,amount,typeEntry', $csv);
    }

    public function test_import_csv_upserts(): void
    {
        $csv = implode("\n", [
            'id,code,description,amount,typeEntry,createdAt,updatedAt',
            Str::uuid().',T1,Desc,100,DEBIT,,',
            ',T2,Desc2,200,CREDIT,,',
        ]);
        $file = UploadedFile::fake()->createWithContent('entries.csv', $csv);

        $res = $this->post('/api/transaction-entries/import', ['file' => $file]);
        $res->assertOk()->assertJson(['status' => 'imported']);

        $this->assertDatabaseHas('transaction_entry', ['code' => 'T1', 'amount' => 100]);
        $this->assertDatabaseHas('transaction_entry', ['code' => 'T2', 'amount' => 200]);
    }
}

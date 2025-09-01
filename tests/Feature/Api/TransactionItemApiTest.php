<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\TransactionItem;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Feature tests for TransactionItem API endpoints.
 */
class TransactionItemApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated_items(): void
    {
        TransactionItem::factory()->count(3)->create(['deletedAt' => null]);

        $res = $this->getJson('/api/transaction-items?per_page=2');

        $res->assertOk()
            ->assertJsonStructure(['data','links','meta'])
            ->assertJsonPath('meta.per_page', 2);
    }

    public function test_index_supports_filter_and_sort(): void
    {
        TransactionItem::factory()->create(['label' => 'Apple', 'notes' => 'note a', 'isDirty' => 1, 'updatedAt' => now()->subDay()->toISOString()]);
        TransactionItem::factory()->create(['label' => 'Banana', 'notes' => 'note b', 'isDirty' => 0, 'updatedAt' => now()->toISOString()]);
        TransactionItem::factory()->create(['label' => 'Cherry', 'notes' => 'nope', 'isDirty' => 1]);

        $res = $this->getJson('/api/transaction-items?q=note&isDirty=1&sort=label:asc&per_page=50');

        $res->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.label', 'Apple');
    }

    public function test_store_creates_item(): void
    {
        $payload = ['label' => 'Item A', 'quantity' => 2, 'unitPrice' => 100, 'total' => 200];

        $res = $this->postJson('/api/transaction-items', $payload);

        $res->assertStatus(201)
            ->assertJsonPath('data.label', 'Item A')
            ->assertJsonPath('data.total', 200);

        $this->assertDatabaseHas('transaction_item', ['label' => 'Item A', 'total' => 200]);
    }

    public function test_store_validates_bad_payload(): void
    {
        $payload = ['label' => null, 'quantity' => -1];

        $this->postJson('/api/transaction-items', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['label','quantity']);
    }

    public function test_show_returns_resource(): void
    {
        $row = TransactionItem::factory()->create();
        $this->getJson("/api/transaction-items/{$row->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $row->id);
    }

    public function test_update_modifies_item(): void
    {
        $row = TransactionItem::factory()->create(['label' => 'Old']);

        $this->putJson("/api/transaction-items/{$row->id}", ['label' => 'New'])
            ->assertOk()
            ->assertJsonPath('data.label', 'New');

        $this->assertDatabaseHas('transaction_item', ['id' => $row->id, 'label' => 'New']);
    }

    public function test_patch_partial_update_works(): void
    {
        $row = TransactionItem::factory()->create(['notes' => null]);

        $this->patchJson("/api/transaction-items/{$row->id}", ['notes' => 'Some'])
            ->assertOk()
            ->assertJsonPath('data.notes', 'Some');

        $this->assertDatabaseHas('transaction_item', ['id' => $row->id, 'notes' => 'Some']);
    }

    public function test_destroy_soft_deletes(): void
    {
        $row = TransactionItem::factory()->create();

        $this->deleteJson("/api/transaction-items/{$row->id}")
            ->assertOk();

        $this->assertNotNull(TransactionItem::find($row->id)->deletedAt);
    }

    public function test_restore_undeletes(): void
    {
        $row = TransactionItem::factory()->create(['deletedAt' => now()->toISOString()]);

        $this->postJson("/api/transaction-items/{$row->id}/restore")
            ->assertOk()
            ->assertJsonPath('data.id', $row->id);

        $this->assertNull(TransactionItem::find($row->id)->deletedAt);
    }

    public function test_bulk_upsert_creates_updates_and_deletes(): void
    {
        $existing = TransactionItem::factory()->create(['label' => 'Exist', 'notes' => 'before']);
        $toDelete = TransactionItem::factory()->create(['label' => 'DelMe', 'deletedAt' => null]);

        $payload = [
            'items' => [
                ['type' => 'UPDATE', 'id' => $existing->id, 'label' => 'Exist-Up', 'notes' => 'after', 'quantity' => 3],
                ['type' => 'CREATE', 'label' => 'New-001', 'quantity' => 1, 'unitPrice' => 50, 'total' => 50],
                ['type' => 'DELETE', 'id' => $toDelete->id],
            ],
        ];

        $res = $this->postJson('/api/transaction-items/bulk', $payload);
        $res->assertOk()->assertJsonCount(3, 'data');

        $this->assertDatabaseHas('transaction_item', ['id' => $existing->id, 'label' => 'Exist-Up', 'notes' => 'after', 'quantity' => 3]);
        $this->assertDatabaseHas('transaction_item', ['label' => 'New-001', 'total' => 50]);
        $this->assertNotNull(TransactionItem::find($toDelete->id)->deletedAt);
    }

    public function test_bulk_requires_id_for_update_and_delete(): void
    {
        $payload = ['items' => [['type' => 'UPDATE', 'label' => 'No-ID']]];
        $this->postJson('/api/transaction-items/bulk', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.id']);

        $payload = ['items' => [['type' => 'DELETE']]];
        $this->postJson('/api/transaction-items/bulk', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.id']);
    }

    public function test_export_csv_downloads(): void
    {
        TransactionItem::factory()->count(2)->create();

        $res = $this->get('/api/transaction-items/export');

        $res->assertOk();
        $this->assertStringContainsString('text/csv', $res->headers->get('content-type'));
        $csv = $res->streamedContent();
        $this->assertStringContainsString('id,transactionId,productId,remoteId,localId,label,quantity,unitId,unitPrice,total,notes', $csv);
    }

    public function test_import_csv_upserts(): void
    {
        $csv = implode("\n", [
            'id,transactionId,productId,label,quantity,unitPrice,total,notes,createdAt,updatedAt',
            Str::uuid().',T1,P1,Item1,2,100,200,ok,,',
            ',T2,P2,Item2,1,50,50,note,,',
        ]);

        $file = UploadedFile::fake()->createWithContent('transaction_items.csv', $csv);

        $res = $this->post('/api/transaction-items/import', ['file' => $file]);

        $res->assertOk()->assertJson(['status' => 'imported']);
        $this->assertDatabaseHas('transaction_item', ['label' => 'Item1', 'total' => 200]);
        $this->assertDatabaseHas('transaction_item', ['label' => 'Item2', 'total' => 50]);
    }

    public function test_import_rejects_non_csv(): void
    {
        $file = UploadedFile::fake()->create('data.json', 5, 'application/json');

        $this->post('/api/transaction-items/import', ['file' => $file], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_pagination_meta_links_present(): void
    {
        TransactionItem::factory()->count(25)->create();

        $this->getJson('/api/transaction-items?per_page=5&page=2')
            ->assertOk()
            ->assertJsonStructure([
                'data',
                'links' => ['first','last','prev','next'],
                'meta'  => ['current_page','per_page','total','last_page']
            ])
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.per_page', 5);
    }
}

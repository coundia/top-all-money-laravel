<?php

namespace Tests\Feature\Api;

use Tests\ApiTestCase;
use Tests\TestCase;
use App\Models\Account;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountApiTest extends ApiTestCase
{

    public function test_index_returns_paginated_accounts(): void
    {
        Account::factory()->count(3)->create(['deletedAt' => null]);

        $res = $this->getJson('/api/accounts?per_page=2');

        $res->assertStatus(200)
            ->assertJsonStructure(['data','links','meta'])
            ->assertJsonPath('meta.per_page', 2);
    }


    public function test_index_supports_filter_and_sort(): void
    {
        Account::factory()->create(['code' => 'ACC-AAA', 'description' => 'hello', 'isDirty' => 1, 'updatedAt' => now()->subDay()->toISOString()]);
        Account::factory()->create(['code' => 'ACC-ZZZ', 'description' => 'hello world', 'isDirty' => 0, 'updatedAt' => now()->toISOString()]);
        Account::factory()->create(['code' => 'BLAH-123', 'description' => 'nope', 'isDirty' => 1]);

        // Filtre q="hello" + isDirty=1 + tri code:asc
        $res = $this->getJson('/api/accounts?q=hello&isDirty=1&sort=code:asc&per_page=50');

        $res->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'ACC-AAA');
    }


    public function test_store_creates_account(): void
    {
        $payload = ['code' => 'ACC-001', 'currency' => 'XOF', 'description' => 'Main'];

        $res = $this->postJson('/api/accounts', $payload);

        $res->assertStatus(201)
            ->assertJsonPath('data.code', 'ACC-001')
            ->assertJsonPath('data.currency', 'XOF');
        $this->assertDatabaseHas('account', ['code' => 'ACC-001']);
    }


    public function test_store_validates_bad_payload(): void
    {
        // isDefault doit être booléen
        $payload = ['isDefault' => 'abc'];

        $this->postJson('/api/accounts', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['isDefault']);
    }


    public function test_show_returns_resource(): void
    {
        $a = Account::factory()->create();
        $this->getJson("/api/accounts/{$a->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $a->id);
    }


    public function test_update_modifies_account(): void
    {
        $a = Account::factory()->create(['description' => 'Old']);

        $this->putJson("/api/accounts/{$a->id}", ['description' => 'Updated'])
            ->assertOk()
            ->assertJsonPath('data.description', 'Updated');

        $this->assertDatabaseHas('account', ['id' => $a->id, 'description' => 'Updated']);
    }


    public function test_patch_partial_update_works(): void
    {
        $a = Account::factory()->create(['status' => null]);

        $this->patchJson("/api/accounts/{$a->id}", ['status' => 'ACTIVE'])
            ->assertOk()
            ->assertJsonPath('data.status', 'ACTIVE');

        $this->assertDatabaseHas('account', ['id' => $a->id, 'status' => 'ACTIVE']);
    }


    public function test_destroy_soft_deletes(): void
    {
        $a = Account::factory()->create();

        $this->deleteJson("/api/accounts/{$a->id}")
            ->assertOk();

        $this->assertNotNull(Account::find($a->id)->deletedAt);
    }


    public function restore_undeletes(): void
    {
        $a = Account::factory()->create(['deletedAt' => now()->toISOString()]);

        $this->postJson("/api/accounts/{$a->id}/restore")
            ->assertOk()
            ->assertJsonPath('data.id', $a->id);

        $this->assertNull(Account::find($a->id)->deletedAt);
    }


    public function test_bulk_upsert_creates_and_updates_and_deletes(): void
    {
        $existing = Account::factory()->create(['code' => 'EXIST', 'description' => 'before']);
        $toDelete = Account::factory()->create(['code' => 'DELME', 'deletedAt' => null]);

        $payload = [
            'items' => [
                // UPDATE
                ['type' => 'UPDATE', 'id' => $existing->id, 'code' => 'EXIST-UP', 'description' => 'after'],
                // CREATE (sans id)
                ['type' => 'CREATE', 'code' => 'NEW-001', 'currency' => 'XOF'],
                // DELETE
                ['type' => 'DELETE', 'id' => $toDelete->id],
            ],
        ];

        $res = $this->postJson('/api/accounts/bulk', $payload);
        $res->assertOk()->assertJsonCount(3, 'data');

        $this->assertDatabaseHas('account', ['id' => $existing->id, 'code' => 'EXIST-UP', 'description' => 'after']);
        $this->assertDatabaseHas('account', ['code' => 'NEW-001', 'currency' => 'XOF']);
        $this->assertNotNull(Account::find($toDelete->id)->deletedAt);
    }


    public function test_bulk_requires_id_for_update_and_delete(): void
    {
        // UPDATE sans id
        $payload = ['items' => [['type' => 'UPDATE', 'code' => 'NO-ID']]];
        $this->postJson('/api/accounts/bulk', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.id']);

        // DELETE sans id
        $payload = ['items' => [['type' => 'DELETE']]];
        $this->postJson('/api/accounts/bulk', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.id']);
    }


    public function test_export_csv_downloads(): void
    {
        Account::factory()->count(2)->create();

        $res = $this->get('/api/accounts/export');

        $res->assertOk();
        $this->assertStringContainsString('text/csv', $res->headers->get('content-type'));
        $csv = $res->streamedContent();
        $this->assertStringContainsString('id,remoteId,localId,code,description,currency,status', $csv);
    }


    public function test_import_csv_upserts(): void
    {
        $csv = implode("\n", [
            'id,code,currency,description,isDefault,createdAt,updatedAt',
            Str::uuid().',A1,XOF,Desc,0,,',
            ',A2,USD,Desc2,1,,',
        ]);

        $file = UploadedFile::fake()->createWithContent('accounts.csv', $csv);

        $res = $this->post('/api/accounts/import', ['file' => $file]);

        $res->assertOk()->assertJson(['status' => 'imported']);
        $this->assertDatabaseHas('account', ['code' => 'A1']);
        $this->assertDatabaseHas('account', ['code' => 'A2']);
    }


    public function test_import_rejects_non_csv(): void
    {
        $file = UploadedFile::fake()->create('data.json', 5, 'application/json');

        $this->post('/api/accounts/import', ['file' => $file], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }



    public function pagination_meta_links_present(): void
    {
        Account::factory()->count(30)->create();

        $this->getJson('/api/accounts?per_page=5&page=2')
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

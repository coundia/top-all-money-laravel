<?php
// This test suite covers CRUD, filtering, bulk, import/export for Product.

namespace Tests\Feature\Api;

use Tests\ApiTestCase;
use Tests\TestCase;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductApiTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated(): void
    {
        Product::factory()->count(3)->create(['deletedAt' => null]);
        $res = $this->getJson('/api/products?per_page=2');

        $res->assertOk()
            ->assertJsonStructure(['data','links','meta'])
            ->assertJsonPath('meta.per_page', 2);
    }

    public function test_index_supports_filter_and_sort(): void
    {
        Product::factory()->create(['code' => 'PR-AAA', 'name' => 'Alpha', 'isDirty' => 1, 'updatedAt' => now()->subDay()->toISOString()]);
        Product::factory()->create(['code' => 'PR-ZZZ', 'name' => 'Alpha Pro', 'isDirty' => 0, 'updatedAt' => now()->toISOString()]);
        Product::factory()->create(['code' => 'OTHER-123', 'name' => 'Nope', 'isDirty' => 1]);

        $res = $this->getJson('/api/products?q=Alpha&isDirty=1&sort=code:asc&per_page=50');

        $res->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'PR-AAA');
    }

    public function test_store_creates(): void
    {
        $payload = ['code' => 'PR-001', 'name' => 'Widget', 'defaultPrice' => 1000];
        $res = $this->postJson('/api/products', $payload);

        $res->assertStatus(201)
            ->assertJsonPath('data.code', 'PR-001')
            ->assertJsonPath('data.name', 'Widget')
            ->assertJsonPath('data.defaultPrice', 1000);

        $this->assertDatabaseHas('product', ['code' => 'PR-001']);
    }

    public function test_store_validates(): void
    {
        $this->postJson('/api/products', ['code' => 'OK'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_update_modifies(): void
    {
        $p = Product::factory()->create(['name' => 'Old']);
        $this->putJson("/api/products/{$p->id}", ['name' => 'Updated'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated');

        $this->assertDatabaseHas('product', ['id' => $p->id, 'name' => 'Updated']);
    }

    public function test_destroy_soft_deletes(): void
    {
        $p = Product::factory()->create();
        $this->deleteJson("/api/products/{$p->id}")->assertOk();
        $this->assertNotNull(Product::find($p->id)->deletedAt);
    }

    public function test_restore_undeletes(): void
    {
        $p = Product::factory()->create(['deletedAt' => now()->toISOString()]);
        $this->postJson("/api/products/{$p->id}/restore")
            ->assertOk()
            ->assertJsonPath('data.id', $p->id);
        $this->assertNull(Product::find($p->id)->deletedAt);
    }

    public function test_bulk_upsert_creates_updates_deletes(): void
    {
        $existing = Product::factory()->create(['code' => 'EXIST', 'name' => 'before']);
        $toDelete = Product::factory()->create(['code' => 'DELME', 'deletedAt' => null]);

        $payload = [
            'items' => [
                ['type' => 'UPDATE', 'id' => $existing->id, 'code' => 'EXIST-UP', 'name' => 'after'],
                ['type' => 'CREATE', 'code' => 'NEW-001', 'name' => 'New', 'defaultPrice' => 100],
                ['type' => 'DELETE', 'id' => $toDelete->id],
            ],
        ];

        $res = $this->postJson('/api/products/bulk', $payload);
        $res->assertOk()->assertJsonCount(3, 'data');

        $this->assertDatabaseHas('product', ['id' => $existing->id, 'code' => 'EXIST-UP', 'name' => 'after']);
        $this->assertDatabaseHas('product', ['code' => 'NEW-001', 'name' => 'New']);
        $this->assertNotNull(Product::find($toDelete->id)->deletedAt);
    }

    public function test_export_csv_downloads(): void
    {
        Product::factory()->count(2)->create();
        $res = $this->get('/api/products/export');

        $res->assertOk();
        $this->assertStringContainsString('text/csv', $res->headers->get('content-type'));
        $csv = $res->streamedContent();
        $this->assertStringContainsString('id,remoteId,localId,code,account,name,description', $csv);
    }

    public function test_import_csv_upserts(): void
    {
        $csv = implode("\n", [
            'id,code,name,defaultPrice,createdAt,updatedAt',
            Str::uuid().',P1,Prod1,100,,',
            ',P2,Prod2,200,,',
        ]);
        $file = UploadedFile::fake()->createWithContent('products.csv', $csv);

        $res = $this->post('/api/products/import', ['file' => $file]);
        $res->assertOk()->assertJson(['status' => 'imported']);

        $this->assertDatabaseHas('product', ['code' => 'P1', 'name' => 'Prod1']);
        $this->assertDatabaseHas('product', ['code' => 'P2', 'name' => 'Prod2']);
    }
}

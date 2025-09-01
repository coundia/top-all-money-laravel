<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated_categories(): void
    {
        Category::factory()->count(3)->create(['deletedAt' => null]);

        $res = $this->getJson('/api/categories?per_page=2');

        $res->assertStatus(200)
            ->assertJsonStructure(['data','links','meta'])
            ->assertJsonPath('meta.per_page', 2);
    }

    public function test_index_supports_filter_and_sort(): void
    {
        Category::factory()->create(['code' => 'CAT-AAA', 'description' => 'hello', 'isDirty' => 1, 'updatedAt' => now()->subDay()->toISOString()]);
        Category::factory()->create(['code' => 'CAT-ZZZ', 'description' => 'hello world', 'isDirty' => 0, 'updatedAt' => now()->toISOString()]);
        Category::factory()->create(['code' => 'OTHER-123', 'description' => 'nope', 'isDirty' => 1]);

        $res = $this->getJson('/api/categories?q=hello&isDirty=1&sort=code:asc&per_page=50');

        $res->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'CAT-AAA');
    }

    public function test_store_creates_category(): void
    {
        $payload = ['code' => 'CAT-001', 'description' => 'Main', 'typeEntry' => 'DEBIT'];

        $res = $this->postJson('/api/categories', $payload);

        $res->assertStatus(201)
            ->assertJsonPath('data.code', 'CAT-001')
            ->assertJsonPath('data.typeEntry', 'DEBIT');

        $this->assertDatabaseHas('category', ['code' => 'CAT-001']);
    }

    public function test_store_validates_bad_payload(): void
    {
        $payload = ['typeEntry' => 'XXX'];

        $this->postJson('/api/categories', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['typeEntry']);
    }

    public function test_show_returns_resource(): void
    {
        $c = Category::factory()->create();

        $this->getJson("/api/categories/{$c->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $c->id);
    }

    public function test_update_modifies_category(): void
    {
        $c = Category::factory()->create(['description' => 'Old']);

        $this->putJson("/api/categories/{$c->id}", ['description' => 'Updated'])
            ->assertOk()
            ->assertJsonPath('data.description', 'Updated');

        $this->assertDatabaseHas('category', ['id' => $c->id, 'description' => 'Updated']);
    }

    public function test_patch_partial_update_works(): void
    {
        $c = Category::factory()->create(['typeEntry' => 'DEBIT']);

        $this->patchJson("/api/categories/{$c->id}", ['typeEntry' => 'CREDIT'])
            ->assertOk()
            ->assertJsonPath('data.typeEntry', 'CREDIT');

        $this->assertDatabaseHas('category', ['id' => $c->id, 'typeEntry' => 'CREDIT']);
    }

    public function test_destroy_soft_deletes(): void
    {
        $c = Category::factory()->create();

        $this->deleteJson("/api/categories/{$c->id}")
            ->assertOk();

        $this->assertNotNull(Category::find($c->id)->deletedAt);
    }

    public function test_restore_undeletes(): void
    {
        $c = Category::factory()->create(['deletedAt' => now()->toISOString()]);

        $this->postJson("/api/categories/{$c->id}/restore")
            ->assertOk()
            ->assertJsonPath('data.id', $c->id);

        $this->assertNull(Category::find($c->id)->deletedAt);
    }

    public function test_bulk_upsert_creates_updates_deletes(): void
    {
        $existing = Category::factory()->create(['code' => 'EXIST', 'description' => 'before']);
        $toDelete = Category::factory()->create(['code' => 'DELME', 'deletedAt' => null]);

        $payload = [
            'items' => [
                ['type' => 'UPDATE', 'id' => $existing->id, 'code' => 'EXIST-UP', 'description' => 'after'],
                ['type' => 'CREATE', 'code' => 'NEW-001', 'typeEntry' => 'DEBIT'],
                ['type' => 'DELETE', 'id' => $toDelete->id],
            ],
        ];

        $res = $this->postJson('/api/categories/bulk', $payload);

        $res->assertOk()->assertJsonCount(3, 'data');

        $this->assertDatabaseHas('category', ['id' => $existing->id, 'code' => 'EXIST-UP', 'description' => 'after']);
        $this->assertDatabaseHas('category', ['code' => 'NEW-001', 'typeEntry' => 'DEBIT']);
        $this->assertNotNull(Category::find($toDelete->id)->deletedAt);
    }

    public function test_bulk_requires_id_for_update_and_delete(): void
    {
        $this->postJson('/api/categories/bulk', ['items' => [['type' => 'UPDATE', 'code' => 'NO-ID']]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.id']);

        $this->postJson('/api/categories/bulk', ['items' => [['type' => 'DELETE']]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.id']);
    }

    public function test_export_csv_downloads(): void
    {
        Category::factory()->count(2)->create();

        $res = $this->get('/api/categories/export');

        $res->assertOk();
        $this->assertStringContainsString('text/csv', $res->headers->get('content-type'));
        $csv = $res->streamedContent();
        $this->assertStringContainsString('id,remoteId,localId,code,description,typeEntry,account,createdAt,updatedAt,deletedAt,syncAt,isShared,createdBy,version,isDirty', $csv);
    }

    public function test_import_csv_upserts(): void
    {
        $csv = implode("\n", [
            'id,code,description,typeEntry,account,createdAt,updatedAt,isDirty,version',
            Str::uuid().',C1,Desc,DEBIT,,,' . ',' . '1' . ',' . '0',
            ',C2,Desc2,CREDIT,,,' . ',' . '1' . ',' . '0',
        ]);

        $file = UploadedFile::fake()->createWithContent('categories.csv', $csv);

        $res = $this->post('/api/categories/import', ['file' => $file]);

        $res->assertOk()->assertJson(['status' => 'imported']);
        $this->assertDatabaseHas('category', ['code' => 'C1']);
        $this->assertDatabaseHas('category', ['code' => 'C2']);
    }

    public function test_import_rejects_non_csv(): void
    {
        $file = UploadedFile::fake()->create('data.json', 5, 'application/json');

        $this->post('/api/categories/import', ['file' => $file], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_pagination_meta_links_present(): void
    {
        Category::factory()->count(30)->create();

        $this->getJson('/api/categories?per_page=5&page=2')
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

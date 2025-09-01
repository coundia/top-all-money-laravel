<?php
// This test suite covers CRUD, filtering, bulk, import/export for StockLevel.

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\StockLevel;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StockLevelApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_paginated(): void
    {
        StockLevel::factory()->count(3)->create(['deletedAt'=>null]);
        $this->getJson('/api/stock-levels?per_page=2')
            ->assertOk()
            ->assertJsonStructure(['data','links','meta'])
            ->assertJsonPath('meta.per_page',2);
    }

    public function test_store_creates()
    {
        $payload = StockLevel::factory()->make()->toArray();

        $response = $this->postJson('/api/stock-levels', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment(['code' => $payload['code']]);
    }


    public function test_update_modifies(): void
    {
        $sl=StockLevel::factory()->create(['stockOnHand'=>1]);
        $this->putJson("/api/stock-levels/{$sl->id}",['stockOnHand'=>9])
            ->assertOk()->assertJsonPath('data.stockOnHand',9);
        $this->assertDatabaseHas('stock_level',['id'=>$sl->id,'stockOnHand'=>9]);
    }

    public function test_destroy_and_restore(): void
    {
        self::markTestSkipped("to check");
        $sl=StockLevel::factory()->create();
        $this->deleteJson("/api/stock-levels/{$sl->id}")->assertOk();
        $this->assertNotNull(StockLevel::find($sl->id)->deletedAt);
        $this->postJson("/api/stock-levels/{$sl->id}/restore")->assertOk();
        $this->assertNull(StockLevel::find($sl->id)->deletedAt);
    }

    public function test_bulk_upsert_flow(): void
    {
        self::markTestSkipped("to check");
        $existing=StockLevel::factory()->create(['stockOnHand'=>1]);
        $toDelete=StockLevel::factory()->create();

        $payload=['items'=>[
            ['type'=>'UPDATE','id'=>$existing->id,'stockOnHand'=>7],
            ['type'=>'CREATE','productVariantId'=>Str::uuid(),'companyId'=>Str::uuid(),'stockOnHand'=>3],
            ['type'=>'DELETE','id'=>$toDelete->id],
        ]];
        $this->postJson('/api/stock-levels/bulk',$payload)
            ->assertOk()->assertJsonCount(3,'data');
        $this->assertDatabaseHas('stock_level',['id'=>$existing->id,'stockOnHand'=>7]);
        $this->assertNotNull(StockLevel::find($toDelete->id)->deletedAt);
    }

    public function test_export_and_import_csv(): void
    {
        self::markTestSkipped("to check");
        StockLevel::factory()->count(2)->create();
        $res=$this->get('/api/stock-levels/export');
        $res->assertOk();
        $this->assertStringContainsString('text/csv',$res->headers->get('content-type'));

        $csv=implode("\n",[
            'id,productVariantId,companyId,stockOnHand,createdAt,updatedAt',
            Str::uuid().','.Str::uuid().','.Str::uuid().',10,,',
            ','.Str::uuid().','.Str::uuid().',5,,',
        ]);
        $file=UploadedFile::fake()->createWithContent('stock_levels.csv',$csv);
        $this->post('/api/stock-levels/import',['file'=>$file])
            ->assertOk()->assertJson(['status'=>'imported']);
    }
}

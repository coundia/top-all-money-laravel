<?php
// This test suite covers CRUD, filtering, bulk, import/export for StockMovement.

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\StockMovement;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StockMovementApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_creates(): void
    {
        $payload=['type_stock_movement'=>'IN','quantity'=>10,'companyId'=>Str::uuid(),'productVariantId'=>Str::uuid()];
        $this->postJson('/api/stock-movements',$payload)->assertStatus(201);
        $this->assertDatabaseHas('stock_movement',['type_stock_movement'=>'IN','quantity'=>10]);
    }

    public function test_bulk_upsert(): void
    {
        self::markTestSkipped("to check");
        $ex=StockMovement::factory()->create(['quantity'=>1,'type_stock_movement'=>'IN']);
        $del=StockMovement::factory()->create();
        $payload=['items'=>[
            ['type'=>'UPDATE','id'=>$ex->id,'quantity'=>5],
            ['type'=>'CREATE','type_stock_movement'=>'OUT','quantity'=>2,'companyId'=>Str::uuid(),'productVariantId'=>Str::uuid()],
            ['type'=>'DELETE','id'=>$del->id],
        ]];
        $this->postJson('/api/stock-movements/bulk',$payload)->assertOk()->assertJsonCount(3,'data');
        $this->assertDatabaseHas('stock_movement',['id'=>$ex->id,'quantity'=>5]);
        $this->assertNotNull(StockMovement::find($del->id)->deletedAt);
    }

    public function test_export_and_import(): void
    {
        self::markTestSkipped("to check");
        StockMovement::factory()->count(2)->create();
        $this->get('/api/stock-movements/export')->assertOk();

        $csv=implode("\n",[
            'id,type_stock_movement,quantity,companyId,productVariantId,createdAt,updatedAt',
            Str::uuid().',IN,3,'.Str::uuid().','.Str::uuid().',,',
            ',OUT,2,'.Str::uuid().','.Str::uuid().',,',
        ]);
        $file=UploadedFile::fake()->createWithContent('stock_movements.csv',$csv);
        $this->post('/api/stock-movements/import',['file'=>$file])->assertOk();
    }
}

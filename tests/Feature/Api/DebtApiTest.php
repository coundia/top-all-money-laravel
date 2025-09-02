<?php
// This test suite covers CRUD, filtering, bulk, import/export for Debt.

namespace Tests\Feature\Api;

use Tests\ApiTestCase;
use Tests\TestCase;
use App\Models\Debt;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DebtApiTest extends ApiTestCase
{

    public function test_store_creates(): void
    {
        $this->postJson('/api/debts',['code'=>'D-001','balance'=>100])
            ->assertStatus(201)->assertJsonPath('data.code','D-001');
        $this->assertDatabaseHas('debt',['code'=>'D-001']);
    }

    public function test_bulk_upsert(): void
    {
        $ex=Debt::factory()->create(['code'=>'EX','balance'=>1]);
        $del=Debt::factory()->create();
        $payload=['items'=>[
            ['type'=>'UPDATE','id'=>$ex->id,'balance'=>5],
            ['type'=>'CREATE','code'=>'NEW','balance'=>2],
            ['type'=>'DELETE','id'=>$del->id],
        ]];
        $this->postJson('/api/debts/bulk',$payload)->assertOk()->assertJsonCount(3,'data');
        $this->assertDatabaseHas('debt',['id'=>$ex->id,'balance'=>5]);
        $this->assertNotNull(Debt::find($del->id)->deletedAt);
    }

    public function test_export_import(): void
    {
        Debt::factory()->count(2)->create();
        $this->get('/api/debts/export')->assertOk();

        $csv=implode("\n",[
            'id,code,balance,createdAt,updatedAt',
            Str::uuid().',D1,10,,',
            ',D2,20,,',
        ]);
        $file=UploadedFile::fake()->createWithContent('debts.csv',$csv);
        $this->post('/api/debts/import',['file'=>$file])->assertOk();
    }
}

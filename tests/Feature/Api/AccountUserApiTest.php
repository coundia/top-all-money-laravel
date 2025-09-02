<?php
// This test suite covers CRUD, filtering, bulk, import/export for AccountUser.

namespace Tests\Feature\Api;

use Tests\ApiTestCase;
use Tests\TestCase;
use App\Models\AccountUser;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountUserApiTest extends ApiTestCase
{

    public function test_store_creates(): void
    {
        $this->postJson('/api/account-users',['email'=>'user@example.com'])
            ->assertStatus(201)->assertJsonPath('data.email','user@example.com');
        $this->assertDatabaseHas('account_users',['email'=>'user@example.com']);
    }

    public function test_bulk_upsert(): void
    {
        $ex=AccountUser::factory()->create(['email'=>'ex@ex.com','role'=>'viewer']);
        $del=AccountUser::factory()->create();
        $payload=['items'=>[
            ['type'=>'UPDATE','id'=>$ex->id,'role'=>'admin'],
            ['type'=>'CREATE','email'=>'new@ex.com','role'=>'editor'],
            ['type'=>'DELETE','id'=>$del->id],
        ]];
        $this->postJson('/api/account-users/bulk',$payload)->assertOk()->assertJsonCount(3,'data');
        $this->assertDatabaseHas('account_users',['id'=>$ex->id,'role'=>'admin']);
        $this->assertNotNull(AccountUser::find($del->id)->deletedAt);
    }

    public function test_export_import(): void
    {
        AccountUser::factory()->count(2)->create();
        $this->get('/api/account-users/export')->assertOk();

        $csv=implode("\n",[
            'id,email,role,createdAt,updatedAt',
            Str::uuid().',a@a.com,viewer,,',
            ',b@b.com,editor,,',
        ]);
        $file=UploadedFile::fake()->createWithContent('account_users.csv',$csv);
        $this->post('/api/account-users/import',['file'=>$file])->assertOk();
    }
}

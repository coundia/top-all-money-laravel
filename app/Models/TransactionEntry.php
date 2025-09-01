<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\UsesUuid;

class TransactionEntry extends Model
{
    use  HasFactory,UsesUuid;

    protected $table = 'transaction_entry';
    protected $primaryKey = 'id';

    protected $fillable = [
        'remoteId','localId','code','description','amount','typeEntry',
        'dateTransaction','status','entityName','entityId',
        'accountId','categoryId','companyId','customerId','debtId',
        'createdAt','updatedAt','deletedAt','syncAt','version',
        'createdBy','isDirty'
    ];

    public $timestamps = false;
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\UsesUuid;

class TransactionEntry extends Model
{
    use HasFactory, UsesUuid;

    protected $table = 'transaction_entry';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'remoteId','localId','code','description','amount','typeEntry',
        'dateTransaction','status','entityName','entityId',
        'accountId','categoryId','companyId','customerId','debtId',
        'createdAt','updatedAt','deletedAt','syncAt','version','createdBy','isDirty',
    ];

    protected $casts = [
        'amount'     => 'integer',
        'isDirty'    => 'boolean',
        'createdAt'  => 'datetime',
        'updatedAt'  => 'datetime',
        'deletedAt'  => 'datetime',
        'syncAt'     => 'datetime',
    ];
}

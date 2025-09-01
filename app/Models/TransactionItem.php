<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\UsesUuid;

class TransactionItem extends Model
{
    use UsesUuid;

    protected $table = 'transaction_item';
    protected $primaryKey = 'id';

    protected $fillable = [
        'transactionId','productId','remoteId','localId','label',
        'quantity','unitId','unitPrice','total','notes',
        'createdAt','updatedAt','deletedAt','account','syncAt',
        'code','createdBy','version','isDirty'
    ];

    public $timestamps = false;
}

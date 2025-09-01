<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\UsesUuid;

class Debt extends Model
{
    use HasFactory,UsesUuid;

    protected $table = 'debt';
    protected $primaryKey = 'id';

    protected $fillable = [
        'remoteId','localId','code','notes','balance','balanceDebt',
        'dueDate','statuses','account','customerId',
        'createdAt','updatedAt','deletedAt','syncAt',
        'createdBy','version','isDirty'
    ];

    public $timestamps = false;
}

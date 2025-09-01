<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\UsesUuid;

class Account extends Model
{
    use UsesUuid;

    protected $table = 'account';
    protected $primaryKey = 'id';

    protected $fillable = [
        'remoteId','localId','balance','balance_prev','balance_blocked',
        'balance_init','balance_goal','balance_limit',
        'dateStartAccount','dateEndAccount','typeAccount',
        'code','description','status','currency','isDefault',
        'createdAt','updatedAt','deletedAt','syncAt','version',
        'isShared','createdBy','isDirty'
    ];

    public $timestamps = false;
}

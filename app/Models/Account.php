<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Traits\UsesUuid;

class Account extends Model
{
    use HasFactory, UsesUuid;

    protected $table = 'account';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'remoteId','localId','code','description','currency','status','typeAccount',
        'dateStartAccount','dateEndAccount','balance','balance_prev','balance_blocked',
        'balance_init','balance_goal','balance_limit','isDefault','isShared','isDirty',
        'version','createdBy','createdAt','updatedAt','deletedAt','syncAt'
    ];
}

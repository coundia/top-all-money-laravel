<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\UsesUuid;

class AccountUser extends Model
{
    use HasFactory,UsesUuid;

    protected $table = 'account_users';
    protected $primaryKey = 'id';

    protected $fillable = [
        'code','account','user','email','phone','identify','role','status',
        'invitedBy','invitedAt','acceptedAt','revokedAt',
        'createdAt','updatedAt','deletedAt','syncAt','version','isDirty',
        'remoteId','createdBy','localId'
    ];

    public $timestamps = false;
}

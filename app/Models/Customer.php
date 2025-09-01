<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\UsesUuid;

class Customer extends Model
{
    use UsesUuid;

    protected $table = 'customer';
    protected $primaryKey = 'id';

    protected $fillable = [
        'remoteId','localId','code','firstName','lastName','fullName',
        'balance','balanceDebt','phone','email','notes','status','companyId',
        'addressLine1','addressLine2','city','region','country','postalCode',
        'createdAt','updatedAt','deletedAt','syncAt',
        'createdBy','account','version','isDirty'
    ];

    public $timestamps = false;
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\UsesUuid;

class Company extends Model
{
    use UsesUuid;

    protected $table = 'company';
    protected $primaryKey = 'id';

    protected $fillable = [
        'remoteId','localId','code','name','description',
        'phone','email','website','taxId','currency',
        'addressLine1','addressLine2','city','region','country','postalCode',
        'isDefault','createdAt','updatedAt','deletedAt','syncAt',
        'createdBy','version','isDirty'
    ];

    public $timestamps = false;
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\UsesUuid;

class StockLevel extends Model
{
    use UsesUuid;

    protected $table = 'stock_level';
    protected $primaryKey = 'id';

    protected $fillable = [
        'remoteId','code','localId','stockOnHand','stockAllocated',
        'productVariantId','syncAt','version','account','isDirty',
        'createdBy','companyId','createdAt','updatedAt'
    ];

    public $timestamps = false;
}

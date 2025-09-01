<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\UsesUuid;

class StockMovement extends Model
{
    use UsesUuid;

    protected $table = 'stock_movement';
    protected $primaryKey = 'id';

    protected $fillable = [
        'type_stock_movement','code','remoteId','localId',
        'quantity','companyId','productVariantId','orderLineId','discriminator',
        'account','syncAt','version','isDirty','createdBy',
        'createdAt','updatedAt'
    ];

    public $timestamps = false;
}

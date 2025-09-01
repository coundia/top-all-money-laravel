<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockLevel extends Model
{
    use HasFactory;

    protected $table = 'stock_level';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = true;
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'id','code','remoteId','localId',
        'stockOnHand','stockAllocated',
        'productVariantId','companyId',
        'syncAt','deletedAt',
        'version','account','isDirty','createdBy',
        'createdAt','updatedAt',
    ];

    protected $casts = [
        'stockOnHand'    => 'integer',
        'stockAllocated' => 'integer',
        'version'        => 'integer',
        'isDirty'        => 'boolean',
        'syncAt'         => 'datetime',
        'deletedAt'      => 'datetime',
        'createdAt'      => 'datetime',
        'updatedAt'      => 'datetime',
    ];

    public function product()
    {
        // Note: column name is productVariantId (historical), but it points to products.id
        return $this->belongsTo(Product::class, 'productVariantId', 'id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'companyId', 'id');
    }
}

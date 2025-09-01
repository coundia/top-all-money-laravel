<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\UsesUuid;

class Product extends Model
{
    use HasFactory, UsesUuid;

    protected $table = 'product';
    protected $primaryKey = 'id';

    protected $fillable = [
        'remoteId','localId','code','account','name','description',
        'barcode','unitId','categoryId','defaultPrice','statuses',
        'purchasePrice','createdAt','updatedAt','deletedAt','syncAt',
        'createdBy','version','isDirty'
    ];

    public $timestamps = false;
}

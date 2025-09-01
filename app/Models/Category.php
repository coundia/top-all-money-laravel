<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\UsesUuid;

class Category extends Model
{
    use UsesUuid;

    protected $table = 'category';
    protected $primaryKey = 'id';

    protected $fillable = [
        'remoteId','localId','code','description','typeEntry',
        'createdAt','updatedAt','deletedAt','syncAt',
        'isShared','createdBy','account','version','isDirty'
    ];

    public $timestamps = false;
}

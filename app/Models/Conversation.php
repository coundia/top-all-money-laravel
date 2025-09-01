<?php

namespace App\Models;

use App\Models\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model {
    use HasFactory,UsesUuid;
    protected $fillable = ['title','user_id'];
    protected $table = 'conversations';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function messages(): HasMany { return $this->hasMany(Message::class)->orderBy('created_at'); }
}

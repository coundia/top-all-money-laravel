<?php

namespace App\Models;

use App\Models\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model {
    use UsesUuid;
    protected $fillable = ['conversation_id','role','content'];
    public function conversation(): BelongsTo { return $this->belongsTo(Conversation::class); }
}

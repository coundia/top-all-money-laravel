<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource transformer for Message model
 */
class MessageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'content' => $this->content,
            'sender' => $this->sender,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

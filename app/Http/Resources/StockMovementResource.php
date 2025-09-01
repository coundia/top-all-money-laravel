<?php
// This resource normalizes StockMovement output types.

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StockMovementResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                  => $this->id,
            'type_stock_movement' => $this->type_stock_movement,
            'code'                => $this->code,
            'remoteId'            => $this->remoteId,
            'localId'             => $this->localId,
            'quantity'            => (int) ($this->quantity ?? 0),
            'companyId'           => $this->companyId,
            'productVariantId'    => $this->productVariantId,
            'orderLineId'         => $this->orderLineId,
            'discriminator'       => $this->discriminator,
            'account'             => $this->account,
            'syncAt'              => $this->syncAt,
            'version'             => (int) ($this->version ?? 0),
            'isDirty'             => (bool) $this->isDirty,
            'createdBy'           => $this->createdBy,
            'createdAt'           => $this->createdAt,
            'updatedAt'           => $this->updatedAt,
            'deletedAt'           => $this->deletedAt,
        ];
    }
}

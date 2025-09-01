<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StockLevelResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'              => $this->id,
            'code'            => $this->code,
            'remoteId'        => $this->remoteId,
            'localId'         => $this->localId,
            'stockOnHand'     => $this->stockOnHand,
            'stockAllocated'  => $this->stockAllocated,
            'productVariantId'=> $this->productVariantId,
            'companyId'       => $this->companyId,
            'version'         => $this->version,
            'account'         => $this->account,
            'isDirty'         => (bool) $this->isDirty,
            'createdBy'       => $this->createdBy,
            'syncAt'          => optional($this->syncAt)->toISOString(),
            'deletedAt'       => optional($this->deletedAt)->toISOString(),
            'createdAt'       => optional($this->createdAt)->toISOString(),
            'updatedAt'       => optional($this->updatedAt)->toISOString(),
        ];
    }
}

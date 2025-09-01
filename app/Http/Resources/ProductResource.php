<?php
// This resource normalizes Product output types.

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'remoteId'      => $this->remoteId,
            'localId'       => $this->localId,
            'code'          => $this->code,
            'account'       => $this->account,
            'name'          => $this->name,
            'description'   => $this->description,
            'barcode'       => $this->barcode,
            'unitId'        => $this->unitId,
            'categoryId'    => $this->categoryId,
            'defaultPrice'  => (int) ($this->defaultPrice ?? 0),
            'statuses'      => $this->statuses,
            'purchasePrice' => (int) ($this->purchasePrice ?? 0),
            'createdAt'     => $this->createdAt,
            'updatedAt'     => $this->updatedAt,
            'deletedAt'     => $this->deletedAt,
            'syncAt'        => $this->syncAt,
            'createdBy'     => $this->createdBy,
            'version'       => (int) ($this->version ?? 0),
            'isDirty'       => (bool) $this->isDirty,
        ];
    }
}

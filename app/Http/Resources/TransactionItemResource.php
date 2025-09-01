<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource transformer for TransactionItem model.
 */
class TransactionItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'transactionId' => $this->transactionId,
            'productId'     => $this->productId,
            'remoteId'      => $this->remoteId,
            'localId'       => $this->localId,
            'label'         => $this->label,
            'quantity'      => (int) $this->quantity,
            'unitId'        => $this->unitId,
            'unitPrice'     => (int) $this->unitPrice,
            'total'         => (int) $this->total,
            'notes'         => $this->notes,
            'account'       => $this->account,
            'code'          => $this->code,
            'createdBy'     => $this->createdBy,
            'version'       => (int) $this->version,
            'isDirty'       => (bool) $this->isDirty,
            'createdAt'     => $this->createdAt,
            'updatedAt'     => $this->updatedAt,
            'deletedAt'     => $this->deletedAt,
            'syncAt'        => $this->syncAt,
        ];
    }
}

<?php
// This resource normalizes TransactionEntry output types.

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionEntryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'remoteId'        => $this->remoteId,
            'localId'         => $this->localId,
            'code'            => $this->code,
            'description'     => $this->description,
            'amount'          => (int) ($this->amount ?? 0),
            'typeEntry'       => $this->typeEntry,
            'dateTransaction' => $this->dateTransaction,
            'status'          => $this->status,
            'entityName'      => $this->entityName,
            'entityId'        => $this->entityId,
            'accountId'       => $this->accountId,
            'categoryId'      => $this->categoryId,
            'companyId'       => $this->companyId,
            'customerId'      => $this->customerId,
            'debtId'          => $this->debtId,
            'createdAt'       => $this->createdAt,
            'updatedAt'       => $this->updatedAt,
            'deletedAt'       => $this->deletedAt,
            'syncAt'          => $this->syncAt,
            'version'         => (int) ($this->version ?? 0),
            'createdBy'       => $this->createdBy,
            'isDirty'         => (bool) $this->isDirty,
        ];
    }
}

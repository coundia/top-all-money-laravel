<?php
// This resource normalizes Debt output types.

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DebtResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'remoteId'    => $this->remoteId,
            'localId'     => $this->localId,
            'code'        => $this->code,
            'notes'       => $this->notes,
            'balance'     => (int) ($this->balance ?? 0),
            'balanceDebt' => (int) ($this->balanceDebt ?? 0),
            'dueDate'     => $this->dueDate,
            'statuses'    => $this->statuses,
            'account'     => $this->account,
            'customerId'  => $this->customerId,
            'createdAt'   => $this->createdAt,
            'updatedAt'   => $this->updatedAt,
            'deletedAt'   => $this->deletedAt,
            'syncAt'      => $this->syncAt,
            'createdBy'   => $this->createdBy,
            'version'     => (int) ($this->version ?? 0),
            'isDirty'     => (bool) $this->isDirty,
        ];
    }
}

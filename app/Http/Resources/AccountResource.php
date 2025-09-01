<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    public function toArray(   $request): array{
        return [
            'id'               => $this->id,
            'remoteId'         => $this->remoteId,
            'localId'          => $this->localId,
            'code'             => $this->code,
            'description'      => $this->description,
            'currency'         => $this->currency,
            'status'           => $this->status,
            'typeAccount'      => $this->typeAccount,
            'dateStartAccount' => $this->dateStartAccount,
            'dateEndAccount'   => $this->dateEndAccount,
            'balance'          => (int) $this->balance,
            'balance_prev'     => (int) $this->balance_prev,
            'balance_blocked'  => (int) $this->balance_blocked,
            'balance_init'     => (int) $this->balance_init,
            'balance_goal'     => (int) $this->balance_goal,
            'balance_limit'    => (int) $this->balance_limit,
            'isDefault'        => (bool) $this->isDefault,
            'isShared'         => (bool) $this->isShared,
            'isDirty'          => (bool) $this->isDirty,
            'version'          => (int) $this->version,
            'createdBy'        => $this->createdBy,
            'createdAt'        => $this->createdAt,
            'updatedAt'        => $this->updatedAt,
            'deletedAt'        => $this->deletedAt,
            'syncAt'           => $this->syncAt,
        ];
    }
}

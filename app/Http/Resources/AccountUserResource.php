<?php
// This resource normalizes AccountUser output types.

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountUserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'code'       => $this->code,
            'account'    => $this->account,
            'user'       => $this->user,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'identify'   => $this->identify,
            'role'       => $this->role,
            'status'     => $this->status,
            'invitedBy'  => $this->invitedBy,
            'invitedAt'  => $this->invitedAt,
            'acceptedAt' => $this->acceptedAt,
            'revokedAt'  => $this->revokedAt,
            'createdAt'  => $this->createdAt,
            'updatedAt'  => $this->updatedAt,
            'deletedAt'  => $this->deletedAt,
            'syncAt'     => $this->syncAt,
            'version'    => (int) ($this->version ?? 0),
            'isDirty'    => (bool) $this->isDirty,
            'remoteId'   => $this->remoteId,
            'createdBy'  => $this->createdBy,
            'localId'    => $this->localId,
        ];
    }
}

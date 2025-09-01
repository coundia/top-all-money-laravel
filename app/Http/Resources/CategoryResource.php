<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *   schema="CategoryResource",
 *   type="object",
 *   @OA\Property(property="id", type="string", format="uuid"),
 *   @OA\Property(property="remoteId", type="string", nullable=true),
 *   @OA\Property(property="localId", type="string", nullable=true),
 *   @OA\Property(property="code", type="string", nullable=true),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="typeEntry", type="string", enum={"DEBIT","CREDIT"}, nullable=true),
 *   @OA\Property(property="account", type="string", nullable=true),
 *   @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="deletedAt", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="syncAt", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="isShared", type="boolean"),
 *   @OA\Property(property="createdBy", type="string", nullable=true),
 *   @OA\Property(property="version", type="integer"),
 *   @OA\Property(property="isDirty", type="boolean")
 * )
 */
class CategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'remoteId'   => $this->remoteId,
            'localId'    => $this->localId,
            'code'       => $this->code,
            'description'=> $this->description,
            'typeEntry'  => $this->typeEntry,
            'account'    => $this->account,
            'createdAt'  => $this->createdAt,
            'updatedAt'  => $this->updatedAt,
            'deletedAt'  => $this->deletedAt,
            'syncAt'     => $this->syncAt,
            'isShared'   => (bool) $this->isShared,
            'createdBy'  => $this->createdBy,
            'version'    => (int) ($this->version ?? 0),
            'isDirty'    => (bool) $this->isDirty,
        ];
    }
}

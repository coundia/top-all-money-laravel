<?php

namespace App\OpenApi;

/**
 * @OA\Schema(
 *   schema="Account",
 *   type="object",
 *   required={"id"},
 *   @OA\Property(property="id", type="string", example="e3a9f8e0-1f8d-4f5e-9c8f-7a6b2d1c4e90"),
 *   @OA\Property(property="remoteId", type="string", nullable=true),
 *   @OA\Property(property="localId", type="string", nullable=true),
 *   @OA\Property(property="code", type="string", example="ACC-001", nullable=true),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="currency", type="string", example="XOF", nullable=true),
 *   @OA\Property(property="status", type="string", nullable=true),
 *   @OA\Property(property="typeAccount", type="string", nullable=true),
 *   @OA\Property(property="dateStartAccount", type="string", nullable=true),
 *   @OA\Property(property="dateEndAccount", type="string", nullable=true),
 *   @OA\Property(property="balance", type="integer", example=0),
 *   @OA\Property(property="balance_prev", type="integer", example=0),
 *   @OA\Property(property="balance_blocked", type="integer", example=0),
 *   @OA\Property(property="balance_init", type="integer", example=0),
 *   @OA\Property(property="balance_goal", type="integer", example=0),
 *   @OA\Property(property="balance_limit", type="integer", example=0),
 *   @OA\Property(property="isDefault", type="boolean", example=false),
 *   @OA\Property(property="isShared", type="boolean", example=false),
 *   @OA\Property(property="createdBy", type="string", nullable=true),
 *   @OA\Property(property="isDirty", type="boolean", example=true),
 *   @OA\Property(property="version", type="integer", example=0),
 *   @OA\Property(property="createdAt", type="string", example="2025-09-01T10:00:00Z"),
 *   @OA\Property(property="updatedAt", type="string", example="2025-09-01T10:00:00Z"),
 *   @OA\Property(property="deletedAt", type="string", nullable=true),
 *   @OA\Property(property="syncAt", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *   schema="AccountCreateRequest",
 *   type="object",
 *   @OA\Property(property="code", type="string", example="ACC-001"),
 *   @OA\Property(property="currency", type="string", example="XOF"),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="isDefault", type="boolean", example=false)
 * )
 *
 * @OA\Schema(
 *   schema="AccountUpdateRequest",
 *   type="object",
 *   @OA\Property(property="code", type="string", example="ACC-002"),
 *   @OA\Property(property="currency", type="string", example="XOF"),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="isDefault", type="boolean", example=true),
 *   @OA\Property(property="status", type="string", example="ACTIVE")
 * )
 */
class Schemas {}

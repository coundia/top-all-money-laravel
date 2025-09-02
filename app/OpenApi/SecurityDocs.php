<?php

namespace App\OpenApi;

/**
 * @OA\Tag(name="Auth")
 * @OA\Tag(name="Users")
 * @OA\Tag(name="Roles")
 * @OA\Tag(name="Permissions")
 *
 *
 * @OA\Schema(
 *   schema="User",
 *   type="object",
 *   required={"id","email"},
 *   @OA\Property(property="id", type="string", format="uuid"),
 *   @OA\Property(property="name", type="string", nullable=true),
 *   @OA\Property(property="email", type="string", format="email"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *   schema="LoginRequest",
 *   type="object",
 *   required={"email","password"},
 *   @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *   @OA\Property(property="password", type="string", format="password", example="secret"),
 *   @OA\Property(property="device_name", type="string", example="iPhone 15")
 * )
 *
 * @OA\Schema(
 *   schema="RegisterRequest",
 *   type="object",
 *   required={"name","email","password","password_confirmation"},
 *   @OA\Property(property="name", type="string"),
 *   @OA\Property(property="email", type="string", format="email"),
 *   @OA\Property(property="password", type="string", format="password"),
 *   @OA\Property(property="password_confirmation", type="string", format="password")
 * )
 *
 * @OA\Schema(
 *   schema="TokenResponse",
 *   type="object",
 *   required={"accessToken","tokenType"},
 *   @OA\Property(property="accessToken", type="string", example="1|d39e..."),
 *   @OA\Property(property="tokenType", type="string", example="Bearer"),
 *   @OA\Property(property="expiresIn", type="integer", nullable=true, example=3600),
 *   @OA\Property(property="user", ref="#/components/schemas/User")
 * )
 *
 * @OA\Schema(
 *   schema="ForgotPasswordRequest",
 *   type="object",
 *   required={"email"},
 *   @OA\Property(property="email", type="string", format="email")
 * )
 *
 * @OA\Schema(
 *   schema="ResetPasswordRequest",
 *   type="object",
 *   required={"email","token","password","password_confirmation"},
 *   @OA\Property(property="email", type="string", format="email"),
 *   @OA\Property(property="token", type="string"),
 *   @OA\Property(property="password", type="string", format="password"),
 *   @OA\Property(property="password_confirmation", type="string", format="password")
 * )
 *
 * @OA\Schema(
 *   schema="UpdatePasswordRequest",
 *   type="object",
 *   required={"current_password","password","password_confirmation"},
 *   @OA\Property(property="current_password", type="string", format="password"),
 *   @OA\Property(property="password", type="string", format="password"),
 *   @OA\Property(property="password_confirmation", type="string", format="password")
 * )
 *
 * @OA\Schema(
 *   schema="Role",
 *   type="object",
 *   required={"id","name"},
 *   @OA\Property(property="id", type="string", format="uuid"),
 *   @OA\Property(property="name", type="string", example="admin"),
 *   @OA\Property(property="guard_name", type="string", example="api"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *   schema="Permission",
 *   type="object",
 *   required={"id","name"},
 *   @OA\Property(property="id", type="string", format="uuid"),
 *   @OA\Property(property="name", type="string", example="accounts.read"),
 *   @OA\Property(property="guard_name", type="string", example="api"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *   schema="RoleCreateRequest",
 *   type="object",
 *   required={"name"},
 *   @OA\Property(property="name", type="string", example="manager"),
 *   @OA\Property(property="permissions", type="array", @OA\Items(type="string"), example={"accounts.read","accounts.write"})
 * )
 *
 * @OA\Schema(
 *   schema="RoleUpdateRequest",
 *   type="object",
 *   @OA\Property(property="name", type="string"),
 *   @OA\Property(property="permissions", type="array", @OA\Items(type="string"))
 * )
 *
 * @OA\Schema(
 *   schema="PermissionCreateRequest",
 *   type="object",
 *   required={"name"},
 *   @OA\Property(property="name", type="string", example="transactions.delete")
 * )
 *
 * @OA\Post(
 *   path="/api/auth/register",
 *   tags={"Auth"},
 *   summary="Register",
 *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/RegisterRequest")),
 *   @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/TokenResponse")),
 *   @OA\Response(response=422, description="Validation error")
 * )
 *
 * @OA\Post(
 *   path="/api/auth/login",
 *   tags={"Auth"},
 *   summary="Login",
 *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/LoginRequest")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/TokenResponse")),
 *   @OA\Response(response=401, description="Unauthorized"),
 *   @OA\Response(response=422, description="Validation error")
 * )
 *
 * @OA\Post(
 *   path="/api/auth/logout",
 *   tags={"Auth"},
 *   summary="Logout",
 *   security={{"bearerAuth":{}}},
 *   @OA\Response(response=204, description="No Content"),
 *   @OA\Response(response=401, description="Unauthorized")
 * )
 *
 * @OA\Get(
 *   path="/api/auth/me",
 *   tags={"Auth"},
 *   summary="Current user",
 *   security={{"bearerAuth":{}}},
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/User")),
 *   @OA\Response(response=401, description="Unauthorized")
 * )
 *
 * @OA\Post(
 *   path="/api/auth/forgot-password",
 *   tags={"Auth"},
 *   summary="Send reset link",
 *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ForgotPasswordRequest")),
 *   @OA\Response(response=200, description="OK"),
 *   @OA\Response(response=422, description="Validation error")
 * )
 *
 * @OA\Post(
 *   path="/api/auth/reset-password",
 *   tags={"Auth"},
 *   summary="Reset password",
 *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ResetPasswordRequest")),
 *   @OA\Response(response=200, description="OK"),
 *   @OA\Response(response=422, description="Validation error")
 * )
 *
 * @OA\Post(
 *   path="/api/auth/update-password",
 *   tags={"Auth"},
 *   summary="Update password",
 *   security={{"bearerAuth":{}}},
 *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdatePasswordRequest")),
 *   @OA\Response(response=200, description="OK"),
 *   @OA\Response(response=401, description="Unauthorized"),
 *   @OA\Response(response=422, description="Validation error")
 * )
 *
 * @OA\Get(
 *   path="/api/roles",
 *   tags={"Roles"},
 *   summary="List roles",
 *   security={{"bearerAuth":{}}},
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Role"))),
 *   @OA\Response(response=401, description="Unauthorized")
 * )
 *
 * @OA\Post(
 *   path="/api/roles",
 *   tags={"Roles"},
 *   summary="Create role",
 *   security={{"bearerAuth":{}}},
 *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/RoleCreateRequest")),
 *   @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/Role")) ,
 *   @OA\Response(response=401, description="Unauthorized"),
 *   @OA\Response(response=422, description="Validation error")
 * )
 *
 * @OA\Get(
 *   path="/api/roles/{id}",
 *   tags={"Roles"},
 *   summary="Get role",
 *   security={{"bearerAuth":{}}},
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/Role")),
 *   @OA\Response(response=401, description="Unauthorized"),
 *   @OA\Response(response=404, description="Not found")
 * )
 *
 * @OA\Put(
 *   path="/api/roles/{id}",
 *   tags={"Roles"},
 *   summary="Update role",
 *   security={{"bearerAuth":{}}},
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
 *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/RoleUpdateRequest")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/Role")),
 *   @OA\Response(response=401, description="Unauthorized"),
 *   @OA\Response(response=422, description="Validation error"),
 *   @OA\Response(response=404, description="Not found")
 * )
 *
 * @OA\Delete(
 *   path="/api/roles/{id}",
 *   tags={"Roles"},
 *   summary="Delete role",
 *   security={{"bearerAuth":{}}},
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
 *   @OA\Response(response=204, description="No Content"),
 *   @OA\Response(response=401, description="Unauthorized"),
 *   @OA\Response(response=404, description="Not found")
 * )
 *
 * @OA\Post(
 *   path="/api/roles/{id}/permissions",
 *   tags={"Roles"},
 *   summary="Sync role permissions",
 *   security={{"bearerAuth":{}}},
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
 *   @OA\RequestBody(required=true, @OA\JsonContent(
 *     type="object",
 *     required={"permissions"},
 *     @OA\Property(property="permissions", type="array", @OA\Items(type="string"))
 *   )),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/Role")),
 *   @OA\Response(response=401, description="Unauthorized"),
 *   @OA\Response(response=422, description="Validation error"),
 *   @OA\Response(response=404, description="Not found")
 * )
 *
 * @OA\Get(
 *   path="/api/permissions",
 *   tags={"Permissions"},
 *   summary="List permissions",
 *   security={{"bearerAuth":{}}},
 *   @OA\Response(response=200, description="OK",
 *     @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Permission"))
 *   ),
 *   @OA\Response(response=401, description="Unauthorized")
 * )
 *
 * @OA\Post(
 *   path="/api/permissions",
 *   tags={"Permissions"},
 *   summary="Create permission",
 *   security={{"bearerAuth":{}}},
 *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/PermissionCreateRequest")),
 *   @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/Permission")),
 *   @OA\Response(response=401, description="Unauthorized"),
 *   @OA\Response(response=422, description="Validation error")
 * )
 */
final class SecurityDocs {}

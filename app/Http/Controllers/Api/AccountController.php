<?php

namespace App\Http\Controllers\Api;

use App\Models\Account;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AccountController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/accounts",
     *   summary="Lister les comptes",
     *   tags={"Accounts"},
     *   @OA\Parameter(name="q", in="query", description="Recherche code/description", @OA\Schema(type="string")),
     *   @OA\Parameter(name="isDirty", in="query", description="Filtrer par isDirty (0/1)", @OA\Schema(type="integer", enum={0,1})),
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Account"))
     *   )
     * )
     */
    public function index(Request $request)
    {
        $q = $request->string('q');
        $isDirty = $request->has('isDirty') ? (int) $request->query('isDirty') : null;

        $query = Account::query()->whereNull('deletedAt');

        if ($q->isNotEmpty()) {
            $query->where(function ($sub) use ($q) {
                $sub->where('code', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        if ($isDirty !== null) {
            $query->where('isDirty', $isDirty ? 1 : 0);
        }

        return $query->orderByDesc('updatedAt')->get();
    }

    /**
     * @OA\Post(
     *   path="/api/accounts",
     *   summary="Créer un compte",
     *   tags={"Accounts"},
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/AccountCreateRequest")),
     *   @OA\Response(response=201, description="Créé", @OA\JsonContent(ref="#/components/schemas/Account")),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['nullable','string','max:255'],
            'currency' => ['nullable','string','max:10'],
            'description' => ['nullable','string'],
            'isDefault' => ['nullable','boolean'],
        ]);

        $account = Account::create($data);
        return response()->json($account, 201);
    }

    /**
     * @OA\Get(
     *   path="/api/accounts/{id}",
     *   summary="Afficher un compte",
     *   tags={"Accounts"},
     *   @OA\Parameter(name="id", in="path", required=true, description="UUID du compte", @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/Account")),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(Account $account)
    {
        return $account;
    }

    /**
     * @OA\Put(
     *   path="/api/accounts/{id}",
     *   summary="Mettre à jour un compte",
     *   tags={"Accounts"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, description="UUID du compte", @OA\Schema(type="string", format="uuid")),
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/AccountUpdateRequest")),
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/Account")),
     *   @OA\Response(response=404, description="Not found"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     *
     * @OA\Patch(
     *   path="/api/accounts/{id}",
     *   summary="Modifier partiellement un compte",
     *   tags={"Accounts"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, description="UUID du compte", @OA\Schema(type="string", format="uuid")),
     *   @OA\RequestBody(required=false, @OA\JsonContent(ref="#/components/schemas/AccountUpdateRequest")),
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/Account")),
     *   @OA\Response(response=404, description="Not found"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(Request $request, Account $account)
    {
        $data = $request->validate([
            'code' => ['sometimes','nullable','string','max:255'],
            'currency' => ['sometimes','nullable','string','max:10'],
            'description' => ['sometimes','nullable','string'],
            'isDefault' => ['sometimes','boolean'],
            'status' => ['sometimes','nullable','string','max:255'],
        ]);

        $account->update($data);
        return $account;
    }

    /**
     * @OA\Delete(
     *   path="/api/accounts/{id}",
     *   summary="Supprimer (soft delete) un compte",
     *   tags={"Accounts"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, description="UUID du compte", @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="Supprimé"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function destroy(Account $account)
    {
        $account->update(['deletedAt' => now()]);
        return response()->json(['status' => 'deleted']);
    }
}

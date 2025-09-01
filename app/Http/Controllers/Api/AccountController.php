<?php

namespace App\Http\Controllers\Api;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\AccountResource;
use App\Http\Requests\Account\AccountStoreRequest;
use App\Http\Requests\Account\AccountUpdateRequest;
use App\Http\Requests\Account\AccountBulkUpsertRequest;
use App\Http\Requests\Account\AccountImportRequest;

class AccountController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/accounts",
     *   summary="Lister les comptes",
     *   tags={"Accounts"},
     *   @OA\Parameter(name="q", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="isDirty", in="query", @OA\Schema(type="integer", enum={0,1})),
     *   @OA\Parameter(name="sort", in="query", @OA\Schema(type="string", example="updatedAt:desc")),
     *   @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", minimum=1)),
     *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", minimum=1, maximum=200, default=20)),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function index(Request $request)
    {
        $q = (string) $request->query('q', '');
        $isDirty = $request->has('isDirty') ? (int) $request->query('isDirty') : null;
        $perPage = min(max((int) $request->query('per_page', 20), 1), 200);
        $sort = (string) $request->query('sort', 'updatedAt:desc');
        [$sortCol, $sortDir] = array_pad(explode(':', $sort, 2), 2, 'desc');
        $sortCol = in_array($sortCol, ['updatedAt','createdAt','code','currency','status']) ? $sortCol : 'updatedAt';
        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        $query = Account::query()->whereNull('deletedAt');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('code', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        if ($isDirty !== null) {
            $query->where('isDirty', $isDirty ? 1 : 0);
        }

        $paginator = $query->orderBy($sortCol, $sortDir)->paginate($perPage);
        return AccountResource::collection($paginator);
    }

    /**
     * @OA\Post(
     *   path="/api/accounts",
     *   summary="Créer un compte",
     *   tags={"Accounts"},
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/AccountCreateRequest")),
     *   @OA\Response(response=201, description="Créé")
     * )
     */
    public function store(AccountStoreRequest $request)
    {
        $account = Account::create($request->validated());
        return (new AccountResource($account))->response()->setStatusCode(201);

    }

    /**
     * @OA\Get(
     *   path="/api/accounts/{id}",
     *   summary="Afficher un compte",
     *   tags={"Accounts"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(Account $account)
    {
        return new AccountResource($account);
    }

    /**
     * @OA\Put(
     *   path="/api/accounts/{id}",
     *   summary="Mettre à jour un compte",
     *   tags={"Accounts"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/AccountUpdateRequest")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=404, description="Not found")
     * )
     *
     * @OA\Patch(
     *   path="/api/accounts/{id}",
     *   summary="Modifier partiellement un compte",
     *   tags={"Accounts"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\RequestBody(required=false, @OA\JsonContent(ref="#/components/schemas/AccountUpdateRequest")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function update(AccountUpdateRequest $request, Account $account)
    {
        $account->update($request->validated());
        return new AccountResource($account);
    }

    /**
     * @OA\Delete(
     *   path="/api/accounts/{id}",
     *   summary="Soft delete",
     *   tags={"Accounts"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="Supprimé")
     * )
     */
    public function destroy(Account $account)
    {
        $account->update(['deletedAt' => Carbon::now()->toISOString()]);
        return response()->json(['status' => 'deleted']);
    }

    /**
     * @OA\Post(
     *   path="/api/accounts/{id}/restore",
     *   summary="Restaurer un compte soft-deleted",
     *   tags={"Accounts"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="Restauré")
     * )
     */
    public function restore(Account $account)
    {
        $account->update(['deletedAt' => null]);
        return new AccountResource($account);
    }

    /**
     * @OA\Post(
     *   path="/api/accounts/bulk",
     *   summary="Upsert en masse",
     *   tags={"Accounts"},
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"items"},
     *       @OA\Property(
     *         property="items",
     *         type="array",
     *         @OA\Items(
     *           allOf={
     *             @OA\Schema(ref="#/components/schemas/AccountUpdateRequest"),
     *             @OA\Schema(
     *               @OA\Property(property="id", type="string", format="uuid", nullable=true),
     *               @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 enum={"CREATE","UPDATE","DELETE"},
     *                 description="Action de l'item. Défaut: CREATE (si omis)."
     *               )
     *             )
     *           }
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function bulkUpsert(AccountBulkUpsertRequest $request)
    {
        $now = now()->toISOString();

        // Colonnes stabilisées pour VALUES / ON CONFLICT
        $allColumns = [
            'id','remoteId','localId','code','description','currency','status','typeAccount',
            'dateStartAccount','dateEndAccount','balance','balance_prev','balance_blocked',
            'balance_init','balance_goal','balance_limit','isDefault','isShared','isDirty',
            'version','createdBy','createdAt','updatedAt','deletedAt','syncAt',
        ];

        $toUpsert = [];
        $toDeleteIds = [];

        foreach ($request->validated('items') as $row) {
            $type = strtoupper($row['type'] ?? 'CREATE');

            // DELETE => soft delete
            if ($type === 'DELETE') {
                // validé par la Request: id requis
                $toDeleteIds[] = $row['id'];
                continue; // ne pas upsert cet item
            }

            // CREATE/UPDATE => normalisation + upsert
            if (empty($row['id'])) {
                $row['id'] = (string) \Illuminate\Support\Str::uuid();
                $row['createdAt'] = $row['createdAt'] ?? $now;
            } else {
                // Pour cohérence des colonnes SQLite
                $row['createdAt'] = $row['createdAt'] ?? $now;
            }
            $row['updatedAt'] = $now;

            // Normaliser toutes les colonnes
            $normalized = [];
            foreach ($allColumns as $col) {
                $normalized[$col] = array_key_exists($col, $row) ? $row[$col] : null;
            }
            foreach (['balance','balance_prev','balance_blocked','balance_init','balance_goal','balance_limit','version'] as $intCol) {
                if ($normalized[$intCol] === null) $normalized[$intCol] = 0;
            }
            foreach (['isDefault','isShared','isDirty'] as $boolCol) {
                if ($normalized[$boolCol] === null) $normalized[$boolCol] = 0;
            }

            $toUpsert[] = $normalized;
        }

        // Upsert si nécessaire
        if (!empty($toUpsert)) {
            $updateColumns = array_values(array_diff($allColumns, ['id','createdAt']));
            Account::upsert($toUpsert, ['id'], $updateColumns);
        }

        // Soft delete si nécessaire (priorité au delete si même id se trouve dans les deux)
        if (!empty($toDeleteIds)) {
            Account::whereIn('id', $toDeleteIds)->update([
                'deletedAt' => $now,
                'updatedAt' => $now,
            ]);
        }

        // Retourner l'état final des enregistrements affectés
        $ids = array_merge(array_column($toUpsert, 'id'), $toDeleteIds);
        $fresh = empty($ids) ? collect() : Account::whereIn('id', $ids)->get();

        return AccountResource::collection($fresh);
    }



    /**
     * @OA\Get(
     *   path="/api/accounts/export",
     *   summary="Exporter en CSV",
     *   tags={"Accounts"},
     *   @OA\Parameter(name="q", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="isDirty", in="query", @OA\Schema(type="integer", enum={0,1})),
     *   @OA\Response(response=200, description="CSV", content={
     *     @OA\MediaType(mediaType="text/csv")
     *   })
     * )
     */
    public function export(Request $request): StreamedResponse
    {
        $q = (string) $request->query('q', '');
        $isDirty = $request->has('isDirty') ? (int) $request->query('isDirty') : null;

        $query = Account::query()->whereNull('deletedAt');
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('code', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }
        if ($isDirty !== null) $query->where('isDirty', $isDirty ? 1 : 0);

        $filename = 'accounts_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'id','remoteId','localId','code','description','currency','status','typeAccount',
                'dateStartAccount','dateEndAccount','balance','balance_prev','balance_blocked',
                'balance_init','balance_goal','balance_limit','isDefault','isShared','isDirty',
                'version','createdBy','createdAt','updatedAt','deletedAt','syncAt'
            ]);
            $query->orderBy('updatedAt','desc')->chunk(1000, function ($chunk) use ($out) {
                foreach ($chunk as $a) {
                    fputcsv($out, [
                        $a->id,$a->remoteId,$a->localId,$a->code,$a->description,$a->currency,$a->status,$a->typeAccount,
                        $a->dateStartAccount,$a->dateEndAccount,$a->balance,$a->balance_prev,$a->balance_blocked,
                        $a->balance_init,$a->balance_goal,$a->balance_limit,$a->isDefault,$a->isShared,$a->isDirty,
                        $a->version,$a->createdBy,$a->createdAt,$a->updatedAt,$a->deletedAt,$a->syncAt
                    ]);
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @OA\Post(
     *   path="/api/accounts/import",
     *   summary="Importer un CSV",
     *   tags={"Accounts"},
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     content={
     *       @OA\MediaType(
     *         mediaType="multipart/form-data",
     *         @OA\Schema(
     *           type="object",
     *           required={"file"},
     *           @OA\Property(
     *             property="file",
     *             type="string",
     *             format="binary",
     *             description="Fichier CSV à importer"
     *           )
     *         )
     *       )
     *     }
     *   ),
     *   @OA\Response(response=200, description="Import terminé"),
     *   @OA\Response(response=422, description="Validation error"),
     *   @OA\Response(response=401, description="Unauthorized")
     * )
     */

    public function import(AccountImportRequest $request)
    {
        $file = $request->file('file');
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);
        $map = array_flip($header);
        $now = Carbon::now()->toISOString();
        $batch = [];

        while (($row = fgetcsv($handle)) !== false) {
            $val = fn($k) => array_key_exists($k,$map) ? ($row[$map[$k]] ?? null) : null;
            $item = [
                'id'               => $val('id') ?: (string) Str::uuid(),
                'remoteId'         => $val('remoteId'),
                'localId'          => $val('localId'),
                'code'             => $val('code'),
                'description'      => $val('description'),
                'currency'         => $val('currency'),
                'status'           => $val('status'),
                'typeAccount'      => $val('typeAccount'),
                'dateStartAccount' => $val('dateStartAccount'),
                'dateEndAccount'   => $val('dateEndAccount'),
                'balance'          => (int) ($val('balance') ?? 0),
                'balance_prev'     => (int) ($val('balance_prev') ?? 0),
                'balance_blocked'  => (int) ($val('balance_blocked') ?? 0),
                'balance_init'     => (int) ($val('balance_init') ?? 0),
                'balance_goal'     => (int) ($val('balance_goal') ?? 0),
                'balance_limit'    => (int) ($val('balance_limit') ?? 0),
                'isDefault'        => (int) ($val('isDefault') ?? 0),
                'isShared'         => (int) ($val('isShared') ?? 0),
                'isDirty'          => (int) ($val('isDirty') ?? 1),
                'version'          => (int) ($val('version') ?? 0),
                'createdBy'        => $val('createdBy'),
                'createdAt'        => $val('createdAt') ?: $now,
                'updatedAt'        => $now,
                'deletedAt'        => $val('deletedAt'),
                'syncAt'           => $val('syncAt'),
            ];
            $batch[] = $item;
            if (count($batch) >= 1000) {
                Account::upsert($batch, ['id'], array_keys($item));
                $batch = [];
            }
        }
        if ($batch) Account::upsert($batch, ['id'], array_keys($batch[0]));
        fclose($handle);

        return response()->json(['status' => 'imported']);
    }
}

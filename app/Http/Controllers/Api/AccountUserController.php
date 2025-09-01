<?php
// This controller provides CRUD, soft-delete, bulk upsert, CSV export/import for AccountUser.

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountUser\AccountUserBulkUpsertRequest;
use App\Http\Requests\AccountUser\AccountUserStoreRequest;
use App\Http\Requests\AccountUser\AccountUserUpdateRequest;
use App\Http\Resources\AccountUserResource;
use App\Models\AccountUser;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AccountUserController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/account-users",
     *   summary="List account users",
     *   tags={"AccountUsers"},
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
        $q = (string)$request->query('q','');
        $isDirty = $request->has('isDirty') ? (int)$request->query('isDirty') : null;
        $perPage = min(max((int)$request->query('per_page',20),1),200);
        $sort = (string)$request->query('sort','updatedAt:desc');
        [$sortCol,$sortDir]=array_pad(explode(':',$sort,2),2,'desc');
        $sortCol = in_array($sortCol,['updatedAt','createdAt','email','status','role'])?$sortCol:'updatedAt';
        $sortDir = strtolower($sortDir)==='asc'?'asc':'desc';

        $query = AccountUser::query()->whereNull('deletedAt');

        if($q!==''){
            $query->where(function($s)use($q){
                $s->where('email','like',"%{$q}%")
                    ->orWhere('phone','like',"%{$q}%")
                    ->orWhere('code','like',"%{$q}%");
            });
        }
        if($isDirty!==null) $query->where('isDirty',$isDirty?1:0);

        return AccountUserResource::collection(
            $query->orderBy($sortCol,$sortDir)->paginate($perPage)
        );
    }

    /**
     * @OA\Post(
     *   path="/api/account-users",
     *   summary="Create an account user",
     *   tags={"AccountUsers"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/AccountUserCreateRequest")),
     *   @OA\Response(response=201, description="Created")
     * )
     */
    public function store(AccountUserStoreRequest $request)
    {
        $row = AccountUser::create($request->validated());
        return (new AccountUserResource($row))->response()->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *   path="/api/account-users/{id}",
     *   summary="Show an account user",
     *   tags={"AccountUsers"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function show(AccountUser $accountUser)
    {
        return new AccountUserResource($accountUser);
    }

    /**
     * @OA\Put(
     *   path="/api/account-users/{id}",
     *   summary="Update an account user",
     *   tags={"AccountUsers"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/AccountUserUpdateRequest")),
     *   @OA\Response(response=200, description="OK")
     * )
     * @OA\Patch(
     *   path="/api/account-users/{id}",
     *   summary="Partially update an account user",
     *   tags={"AccountUsers"},
     *   @OA\RequestBody(required=false, @OA\JsonContent(ref="#/components/schemas/AccountUserUpdateRequest")),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function update(AccountUserUpdateRequest $request, AccountUser $accountUser)
    {
        $accountUser->update($request->validated());
        return new AccountUserResource($accountUser);
    }

    /**
     * @OA\Delete(
     *   path="/api/account-users/{id}",
     *   summary="Soft delete",
     *   tags={"AccountUsers"},
     *   @OA\Response(response=200, description="Deleted")
     * )
     */
    public function destroy(AccountUser $accountUser)
    {
        $accountUser->update(['deletedAt'=>Carbon::now()->toISOString()]);
        return response()->json(['status'=>'deleted']);
    }

    /**
     * @OA\Post(
     *   path="/api/account-users/{id}/restore",
     *   summary="Restore an account user",
     *   tags={"AccountUsers"},
     *   @OA\Response(response=200, description="Restored")
     * )
     */
    public function restore(AccountUser $accountUser)
    {
        $accountUser->update(['deletedAt'=>null]);
        return new AccountUserResource($accountUser);
    }

    /**
     * @OA\Post(
     *   path="/api/account-users/bulk",
     *   summary="Bulk upsert account users",
     *   tags={"AccountUsers"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(
     *     type="object", required={"items"},
     *     @OA\Property(property="items", type="array", @OA\Items(
     *       @OA\Schema(
     *         @OA\Property(property="id", type="string", format="uuid", nullable=true),
     *         @OA\Property(property="type", type="string", enum={"CREATE","UPDATE","DELETE"}),
     *         @OA\Property(property="email", type="string", format="email")
     *       )
     *     ))
     *   )),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function bulkUpsert(AccountUserBulkUpsertRequest $request)
    {
        $now = now()->toISOString();

        $columns = [
            'id','code','account','user','email','phone','identify','role','status',
            'invitedBy','invitedAt','acceptedAt','revokedAt',
            'createdAt','updatedAt','deletedAt','syncAt','version','isDirty',
            'remoteId','createdBy','localId'
        ];

        $toUpsert = [];
        $toDeleteIds = [];

        foreach ($request->validated('items') as $row) {
            $type = strtoupper($row['type'] ?? 'CREATE');

            // DELETE -> soft delete
            if ($type === 'DELETE') {
                // validé par la Request: id requis pour DELETE
                $toDeleteIds[] = $row['id'];
                continue;
            }

            // CREATE / UPDATE
            if (empty($row['id'])) {
                $row['id'] = (string) \Illuminate\Support\Str::uuid();
                $row['createdAt'] = $row['createdAt'] ?? $now;
            } else {
                // Si la base est SQLite et que createdAt est NOT NULL, assurez-vous de le poser
                $row['createdAt'] = $row['createdAt'] ?? $now;
            }

            // invitedAt est NOT NULL dans votre schéma -> valeur par défaut
            $row['invitedAt'] = $row['invitedAt'] ?? $now;

            $row['updatedAt'] = $now;

            // Normalisation des colonnes
            $norm = [];
            foreach ($columns as $c) {
                $norm[$c] = array_key_exists($c, $row) ? $row[$c] : null;
            }

            // Valeurs par défaut sur types numériques/booléens
            $norm['version'] = (int) ($norm['version'] ?? 0);
            $norm['isDirty'] = (int) ($norm['isDirty'] ?? 1);

            $toUpsert[] = $norm;
        }

        // Upsert
        if ($toUpsert) {
            $updateCols = array_values(array_diff($columns, ['id','createdAt']));
            \App\Models\AccountUser::upsert($toUpsert, ['id'], $updateCols);
        }

        // Soft delete
        if ($toDeleteIds) {
            \App\Models\AccountUser::whereIn('id', $toDeleteIds)->update([
                'deletedAt' => $now,
                'updatedAt' => $now,
            ]);
        }

        // Renvoyer l’état final
        $ids = array_merge(array_column($toUpsert, 'id'), $toDeleteIds);
        $fresh = $ids ? \App\Models\AccountUser::whereIn('id', $ids)->get() : collect();

        // Si vous avez un Resource dédié, utilisez-le ici. Sinon, retournez tel quel.
        return response()->json(['data' => $fresh->values()]);
    }


    /**
     * @OA\Get(
     *   path="/api/account-users/export",
     *   summary="Export account users as CSV",
     *   tags={"AccountUsers"},
     *   @OA\Response(response=200, description="CSV", content={@OA\MediaType(mediaType="text/csv")})
     * )
     */
    public function export(\Illuminate\Http\Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $q = (string) $request->query('q', '');
        $isDirty = $request->has('isDirty') ? (int) $request->query('isDirty') : null;

        $query = \App\Models\AccountUser::query()->whereNull('deletedAt');
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            });
        }
        if ($isDirty !== null) $query->where('isDirty', $isDirty ? 1 : 0);

        $headers = [
            'id','code','account','user','email','phone','identify','role','status',
            'invitedBy','invitedAt','acceptedAt','revokedAt',
            'createdAt','updatedAt','deletedAt','syncAt','version','isDirty',
            'remoteId','createdBy','localId'
        ];

        $filename = 'account_users_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query, $headers) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            $query->orderBy('updatedAt','desc')->chunk(1000, function ($chunk) use ($out, $headers) {
                foreach ($chunk as $row) {
                    fputcsv($out, array_map(fn($h) => $row->{$h}, $headers));
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }


    /**
     * @OA\Post(
     *   path="/api/account-users/import",
     *   summary="Import account users from CSV",
     *   tags={"AccountUsers"},
     *   @OA\Response(response=200, description="Imported"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */

    public function import(\App\Http\Requests\AccountUser\AccountUserImportRequest $request)
    {
        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);
        $map = array_flip($header);
        $now = now()->toISOString();

        $columns = [
            'id','code','account','user','email','phone','identify','role','status',
            'invitedBy','invitedAt','acceptedAt','revokedAt',
            'createdAt','updatedAt','deletedAt','syncAt','version','isDirty',
            'remoteId','createdBy','localId'
        ];

        $batch = [];
        while (($row = fgetcsv($handle)) !== false) {
            $val = fn($k) => array_key_exists($k,$map) ? ($row[$map[$k]] ?? null) : null;

            $item = [];
            foreach ($columns as $c) {
                $item[$c] = $val($c);
            }

            // Defaults et normalisation
            if (!$item['id']) $item['id'] = (string) \Illuminate\Support\Str::uuid();
            $item['createdAt'] = $item['createdAt'] ?: $now;
            $item['updatedAt'] = $now;

            // invitedAt NOT NULL → default now() si manquant
            $item['invitedAt'] = $item['invitedAt'] ?: $now;

            $item['version'] = (int) ($item['version'] ?? 0);
            $item['isDirty'] = (int) ($item['isDirty'] ?? 1);

            $batch[] = $item;

            if (count($batch) >= 1000) {
                \App\Models\AccountUser::upsert($batch, ['id'], array_diff($columns, ['id','createdAt']));
                $batch = [];
            }
        }

        if ($batch) {
            \App\Models\AccountUser::upsert($batch, ['id'], array_diff($columns, ['id','createdAt']));
        }
        fclose($handle);

        return response()->json(['status' => 'imported']);
    }
}

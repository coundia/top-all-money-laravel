<?php
// This controller provides CRUD, soft-delete, bulk upsert, CSV export/import for TransactionEntry.

namespace App\Http\Controllers\Api;

use App\Models\TransactionEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionEntryResource;
use App\Http\Requests\TransactionEntry\TransactionEntryStoreRequest;
use App\Http\Requests\TransactionEntry\TransactionEntryUpdateRequest;
use App\Http\Requests\TransactionEntry\TransactionEntryBulkUpsertRequest;
use App\Http\Requests\TransactionEntry\TransactionEntryImportRequest;

class TransactionEntryController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/transaction-entries",
     *   summary="List transaction entries",
     *   tags={"TransactionEntries"},
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
        $sortCol = in_array($sortCol, ['updatedAt','createdAt','amount','typeEntry','status','code']) ? $sortCol : 'updatedAt';
        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        $query = TransactionEntry::query()->whereNull('deletedAt');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('code', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }
        if ($isDirty !== null) $query->where('isDirty', $isDirty ? 1 : 0);

        $paginator = $query->orderBy($sortCol, $sortDir)->paginate($perPage);
        return TransactionEntryResource::collection($paginator);
    }

    /**
     * @OA\Post(
     *   path="/api/transaction-entries",
     *   summary="Create a transaction entry",
     *   tags={"TransactionEntries"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/TransactionEntryCreateRequest")),
     *   @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/TransactionEntry"))
     * )
     */
    public function store(TransactionEntryStoreRequest $request)
    {
        $entry = TransactionEntry::create($request->validated());
        return (new TransactionEntryResource($entry))->response()->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *   path="/api/transaction-entries/{id}",
     *   summary="Show a transaction entry",
     *   tags={"TransactionEntries"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/TransactionEntry")),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(TransactionEntry $transactionEntry)
    {
        return new TransactionEntryResource($transactionEntry);
    }

    /**
     * @OA\Put(
     *   path="/api/transaction-entries/{id}",
     *   summary="Update a transaction entry",
     *   tags={"TransactionEntries"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/TransactionEntryUpdateRequest")),
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/TransactionEntry"))
     * )
     * @OA\Patch(
     *   path="/api/transaction-entries/{id}",
     *   summary="Partially update a transaction entry",
     *   tags={"TransactionEntries"},
     *   @OA\RequestBody(required=false, @OA\JsonContent(ref="#/components/schemas/TransactionEntryUpdateRequest")),
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/TransactionEntry"))
     * )
     */
    public function update(TransactionEntryUpdateRequest $request, TransactionEntry $transactionEntry)
    {
        $transactionEntry->update($request->validated());
        return new TransactionEntryResource($transactionEntry);
    }

    /**
     * @OA\Delete(
     *   path="/api/transaction-entries/{id}",
     *   summary="Soft delete",
     *   tags={"TransactionEntries"},
     *   @OA\Response(response=200, description="Deleted")
     * )
     */
    public function destroy(TransactionEntry $transactionEntry)
    {
        $transactionEntry->update(['deletedAt' => Carbon::now()->toISOString()]);
        return response()->json(['status' => 'deleted']);
    }

    /**
     * @OA\Post(
     *   path="/api/transaction-entries/{id}/restore",
     *   summary="Restore a soft-deleted entry",
     *   tags={"TransactionEntries"},
     *   @OA\Response(response=200, description="Restored", @OA\JsonContent(ref="#/components/schemas/TransactionEntry"))
     * )
     */
    public function restore(TransactionEntry $transactionEntry)
    {
        $transactionEntry->update(['deletedAt' => null]);
        return new TransactionEntryResource($transactionEntry);
    }

    /**
     * @OA\Post(
     *   path="/api/transaction-entries/bulk",
     *   summary="Bulk upsert",
     *   tags={"TransactionEntries"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(
     *     type="object", required={"items"},
     *     @OA\Property(property="items", type="array", @OA\Items(
     *       allOf={
     *         @OA\Schema(ref="#/components/schemas/TransactionEntryUpdateRequest"),
     *         @OA\Schema(
     *           @OA\Property(property="id", type="string", format="uuid", nullable=true),
     *           @OA\Property(property="type", type="string", enum={"CREATE","UPDATE","DELETE"})
     *         )
     *       }
     *     ))
     *   )),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function bulkUpsert(TransactionEntryBulkUpsertRequest $request)
    {
        $now = now()->toISOString();
        $columns = [
            'id','remoteId','localId','code','description','amount','typeEntry','dateTransaction','status',
            'entityName','entityId','accountId','categoryId','companyId','customerId','debtId',
            'createdAt','updatedAt','deletedAt','syncAt','version','createdBy','isDirty'
        ];

        $toUpsert = [];
        $toDelete = [];

        foreach ($request->validated('items') as $row) {
            $t = strtoupper($row['type'] ?? 'CREATE');

            if ($t === 'DELETE') {
                $toDelete[] = $row['id'];
                continue;
            }

            if (empty($row['id'])) {
                $row['id'] = (string) Str::uuid();
                $row['createdAt'] = $row['createdAt'] ?? $now;
            } else {
                $row['createdAt'] = $row['createdAt'] ?? $now;
            }
            $row['updatedAt'] = $now;

            // Ensure typeEntry default if DB is NOT NULL
            $row['typeEntry'] = $row['typeEntry'] ?? 'DEBIT';

            $norm = [];
            foreach ($columns as $c) $norm[$c] = array_key_exists($c, $row) ? $row[$c] : null;
            $norm['amount'] = (int) ($norm['amount'] ?? 0);
            $norm['version'] = (int) ($norm['version'] ?? 0);
            $norm['isDirty'] = (int) ($norm['isDirty'] ?? 1);

            $toUpsert[] = $norm;
        }

        if ($toUpsert) {
            $updateCols = array_values(array_diff($columns, ['id','createdAt']));
            TransactionEntry::upsert($toUpsert, ['id'], $updateCols);
        }
        if ($toDelete) {
            TransactionEntry::whereIn('id', $toDelete)->update(['deletedAt' => $now, 'updatedAt' => $now]);
        }

        $ids = array_merge(array_column($toUpsert, 'id'), $toDelete);
        $fresh = $ids ? TransactionEntry::whereIn('id', $ids)->get() : collect();

        return TransactionEntryResource::collection($fresh);
    }

    /**
     * @OA\Get(
     *   path="/api/transaction-entries/export",
     *   summary="Export as CSV",
     *   tags={"TransactionEntries"},
     *   @OA\Response(response=200, description="CSV", content={@OA\MediaType(mediaType="text/csv")})
     * )
     */
    public function export(Request $request): StreamedResponse
    {
        $q = (string) $request->query('q', '');
        $isDirty = $request->has('isDirty') ? (int) $request->query('isDirty') : null;

        $query = TransactionEntry::query()->whereNull('deletedAt');
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('code', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }
        if ($isDirty !== null) $query->where('isDirty', $isDirty ? 1 : 0);

        $filename = 'transaction_entries_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'id','remoteId','localId','code','description','amount','typeEntry','dateTransaction','status',
                'entityName','entityId','accountId','categoryId','companyId','customerId','debtId',
                'createdAt','updatedAt','deletedAt','syncAt','version','createdBy','isDirty'
            ]);
            $query->orderBy('updatedAt','desc')->chunk(1000, function ($chunk) use ($out) {
                foreach ($chunk as $e) {
                    fputcsv($out, [
                        $e->id,$e->remoteId,$e->localId,$e->code,$e->description,$e->amount,$e->typeEntry,$e->dateTransaction,$e->status,
                        $e->entityName,$e->entityId,$e->accountId,$e->categoryId,$e->companyId,$e->customerId,$e->debtId,
                        $e->createdAt,$e->updatedAt,$e->deletedAt,$e->syncAt,$e->version,$e->createdBy,$e->isDirty
                    ]);
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @OA\Post(
     *   path="/api/transaction-entries/import",
     *   summary="Import CSV",
     *   tags={"TransactionEntries"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/TransactionEntryCreateRequest")),
     *   @OA\Response(response=200, description="Imported"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function import(TransactionEntryImportRequest $request)
    {
        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);
        $map = array_flip($header);
        $now = Carbon::now()->toISOString();
        $batch = [];

        $columns = [
            'id','remoteId','localId','code','description','amount','typeEntry','dateTransaction','status',
            'entityName','entityId','accountId','categoryId','companyId','customerId','debtId',
            'createdAt','updatedAt','deletedAt','syncAt','version','createdBy','isDirty'
        ];

        while (($row = fgetcsv($handle)) !== false) {
            $val = fn($k) => array_key_exists($k,$map) ? ($row[$map[$k]] ?? null) : null;

            $item = [];
            foreach ($columns as $c) $item[$c] = $val($c);

            if (!$item['id']) $item['id'] = (string) Str::uuid();
            $item['createdAt'] = $item['createdAt'] ?: $now;
            $item['updatedAt'] = $now;
            $item['typeEntry'] = $item['typeEntry'] ?: 'DEBIT';
            $item['amount'] = (int) ($item['amount'] ?? 0);
            $item['version'] = (int) ($item['version'] ?? 0);
            $item['isDirty'] = (int) ($item['isDirty'] ?? 1);

            $batch[] = $item;
            if (count($batch) >= 1000) {
                TransactionEntry::upsert($batch, ['id'], array_diff($columns, ['id','createdAt']));
                $batch = [];
            }
        }
        if ($batch) TransactionEntry::upsert($batch, ['id'], array_diff($columns, ['id','createdAt']));
        fclose($handle);

        return response()->json(['status' => 'imported']);
    }
}

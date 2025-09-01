<?php

namespace App\Http\Controllers\Api;

use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionItemResource;
use App\Http\Requests\TransactionItem\TransactionItemStoreRequest;
use App\Http\Requests\TransactionItem\TransactionItemUpdateRequest;
use App\Http\Requests\TransactionItem\TransactionItemBulkUpsertRequest;
use App\Http\Requests\TransactionItem\TransactionItemImportRequest;

/**
 * TransactionItemController manages CRUD, bulk upsert, and CSV import/export for transaction items.
 */
class TransactionItemController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/transaction-items",
     *   summary="List transaction items",
     *   tags={"TransactionItems"},
     *   @OA\Parameter(name="q", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="isDirty", in="query", @OA\Schema(type="integer", enum={0,1})),
     *   @OA\Parameter(name="transactionId", in="query", @OA\Schema(type="string", format="uuid")),
     *   @OA\Parameter(name="productId", in="query", @OA\Schema(type="string", format="uuid")),
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
        $transactionId = $request->query('transactionId');
        $productId = $request->query('productId');
        $perPage = min(max((int) $request->query('per_page', 20), 1), 200);
        $sort = (string) $request->query('sort', 'updatedAt:desc');
        [$sortCol, $sortDir] = array_pad(explode(':', $sort, 2), 2, 'desc');
        $allowedSort = ['updatedAt','createdAt','label','quantity','unitPrice','total'];
        $sortCol = in_array($sortCol, $allowedSort) ? $sortCol : 'updatedAt';
        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        $query = TransactionItem::query()->whereNull('deletedAt');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('label', 'like', "%{$q}%")
                    ->orWhere('notes', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            });
        }
        if ($isDirty !== null) $query->where('isDirty', $isDirty ? 1 : 0);
        if ($transactionId) $query->where('transactionId', $transactionId);
        if ($productId) $query->where('productId', $productId);

        $paginator = $query->orderBy($sortCol, $sortDir)->paginate($perPage);
        return TransactionItemResource::collection($paginator);
    }

    /**
     * @OA\Post(
     *   path="/api/transaction-items",
     *   summary="Create a transaction item",
     *   tags={"TransactionItems"},
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/TransactionItemCreateRequest")),
     *   @OA\Response(response=201, description="Created")
     * )
     */
    public function store(TransactionItemStoreRequest $request)
    {
        $row = TransactionItem::create($request->validated());
        return (new TransactionItemResource($row))->response()->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *   path="/api/transaction-items/{id}",
     *   summary="Show a transaction item",
     *   tags={"TransactionItems"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(TransactionItem $transactionItem)
    {
        return new TransactionItemResource($transactionItem);
    }

    /**
     * @OA\Put(
     *   path="/api/transaction-items/{id}",
     *   summary="Update a transaction item",
     *   tags={"TransactionItems"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/TransactionItemUpdateRequest")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=404, description="Not found")
     * )
     * @OA\Patch(
     *   path="/api/transaction-items/{id}",
     *   summary="Partially update a transaction item",
     *   tags={"TransactionItems"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\RequestBody(required=false, @OA\JsonContent(ref="#/components/schemas/TransactionItemUpdateRequest")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function update(TransactionItemUpdateRequest $request, TransactionItem $transactionItem)
    {
        $transactionItem->update($request->validated());
        return new TransactionItemResource($transactionItem);
    }

    /**
     * @OA\Delete(
     *   path="/api/transaction-items/{id}",
     *   summary="Soft delete a transaction item",
     *   tags={"TransactionItems"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="Deleted")
     * )
     */
    public function destroy(TransactionItem $transactionItem)
    {
        $transactionItem->update(['deletedAt' => Carbon::now()->toISOString()]);
        return response()->json(['status' => 'deleted']);
    }

    /**
     * @OA\Post(
     *   path="/api/transaction-items/{id}/restore",
     *   summary="Restore a soft-deleted transaction item",
     *   tags={"TransactionItems"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="Restored")
     * )
     */
    public function restore(TransactionItem $transactionItem)
    {
        $transactionItem->update(['deletedAt' => null]);
        return new TransactionItemResource($transactionItem);
    }

    /**
     * @OA\Post(
     *   path="/api/transaction-items/bulk",
     *   summary="Bulk upsert transaction items",
     *   tags={"TransactionItems"},
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
     *             @OA\Schema(ref="#/components/schemas/TransactionItemUpdateRequest"),
     *             @OA\Schema(
     *               @OA\Property(property="id", type="string", format="uuid", nullable=true),
     *               @OA\Property(property="type", type="string", enum={"CREATE","UPDATE","DELETE"})
     *             )
     *           }
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function bulkUpsert(TransactionItemBulkUpsertRequest $request)
    {
        $now = now()->toISOString();

        $columns = [
            'id','transactionId','productId','remoteId','localId',
            'label','quantity','unitId','unitPrice','total','notes',
            'createdAt','updatedAt','deletedAt','account','syncAt',
            'code','createdBy','version','isDirty'
        ];

        $toUpsert = [];
        $toDeleteIds = [];

        foreach ($request->validated('items') as $row) {
            $type = strtoupper($row['type'] ?? 'CREATE');

            if ($type === 'DELETE') {
                $toDeleteIds[] = $row['id']; // validated by request
                continue;
            }

            if (empty($row['id'])) {
                $row['id'] = (string) Str::uuid();
                $row['createdAt'] = $row['createdAt'] ?? $now;
            } else {
                $row['createdAt'] = $row['createdAt'] ?? $now;
            }
            $row['updatedAt'] = $now;

            $norm = [];
            foreach ($columns as $c) $norm[$c] = array_key_exists($c, $row) ? $row[$c] : null;

            foreach (['quantity','unitPrice','total','version'] as $intCol) {
                if ($norm[$intCol] === null) $norm[$intCol] = 0;
            }
            $norm['isDirty'] = $norm['isDirty'] ?? 1;

            $toUpsert[] = $norm;
        }

        if ($toUpsert) {
            $updateCols = array_values(array_diff($columns, ['id','createdAt']));
            TransactionItem::upsert($toUpsert, ['id'], $updateCols);
        }
        if ($toDeleteIds) {
            TransactionItem::whereIn('id', $toDeleteIds)->update(['deletedAt' => $now, 'updatedAt' => $now]);
        }

        $ids = array_merge(array_column($toUpsert, 'id'), $toDeleteIds);
        $fresh = $ids ? TransactionItem::whereIn('id', $ids)->get() : collect();

        return TransactionItemResource::collection($fresh);
    }

    /**
     * @OA\Get(
     *   path="/api/transaction-items/export",
     *   summary="Export transaction items to CSV",
     *   tags={"TransactionItems"},
     *   @OA\Response(response=200, description="CSV", content={
     *     @OA\MediaType(mediaType="text/csv")
     *   })
     * )
     */
    public function export(Request $request): StreamedResponse
    {
        $q = (string) $request->query('q', '');
        $isDirty = $request->has('isDirty') ? (int) $request->query('isDirty') : null;

        $query = TransactionItem::query()->whereNull('deletedAt');
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('label', 'like', "%{$q}%")
                    ->orWhere('notes', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            });
        }
        if ($isDirty !== null) $query->where('isDirty', $isDirty ? 1 : 0);

        $headers = [
            'id','transactionId','productId','remoteId','localId',
            'label','quantity','unitId','unitPrice','total','notes',
            'createdAt','updatedAt','deletedAt','account','syncAt',
            'code','createdBy','version','isDirty'
        ];

        $filename = 'transaction_items_' . now()->format('Ymd_His') . '.csv';

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
     *   path="/api/transaction-items/import",
     *   summary="Import transaction items from CSV",
     *   tags={"TransactionItems"},
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     content={@OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(type="object", required={"file"},
     *         @OA\Property(property="file", type="string", format="binary")
     *       )
     *     )}
     *   ),
     *   @OA\Response(response=200, description="Imported"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function import(TransactionItemImportRequest $request)
    {
        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);
        $map = array_flip($header);
        $now = now()->toISOString();

        $columns = [
            'id','transactionId','productId','remoteId','localId',
            'label','quantity','unitId','unitPrice','total','notes',
            'createdAt','updatedAt','deletedAt','account','syncAt',
            'code','createdBy','version','isDirty'
        ];

        $batch = [];
        while (($row = fgetcsv($handle)) !== false) {
            $val = fn($k) => array_key_exists($k,$map) ? ($row[$map[$k]] ?? null) : null;

            $item = [];
            foreach ($columns as $c) $item[$c] = $val($c);
            if (!$item['id']) $item['id'] = (string) Str::uuid();
            $item['createdAt'] = $item['createdAt'] ?: $now;
            $item['updatedAt'] = $now;

            foreach (['quantity','unitPrice','total','version'] as $intCol) {
                $item[$intCol] = (int) ($item[$intCol] ?? 0);
            }
            $item['isDirty'] = (int) ($item['isDirty'] ?? 1);

            $batch[] = $item;
            if (count($batch) >= 1000) {
                TransactionItem::upsert($batch, ['id'], array_diff($columns, ['id','createdAt']));
                $batch = [];
            }
        }
        if ($batch) TransactionItem::upsert($batch, ['id'], array_diff($columns, ['id','createdAt']));
        fclose($handle);

        return response()->json(['status' => 'imported']);
    }
}

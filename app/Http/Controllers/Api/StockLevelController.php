<?php

namespace App\Http\Controllers\Api;

use App\Models\StockLevel;
use App\Models\Product;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\StockLevelResource;
use App\Http\Requests\StockLevel\StockLevelStoreRequest;
use App\Http\Requests\StockLevel\StockLevelUpdateRequest;
use App\Http\Requests\StockLevel\StockLevelBulkUpsertRequest;
use App\Http\Requests\StockLevel\StockLevelImportRequest;

class StockLevelController extends Controller
{
    /** Map snake_case to camelCase and filter to fillable. */
    protected function normalizeItem(array $item): array
    {
        $map = [
            'product_variant_id' => 'productVariantId',
            'company_id'         => 'companyId',
            'stock_on_hand'      => 'stockOnHand',
            'stock_allocated'    => 'stockAllocated',
            'remote_id'          => 'remoteId',
            'local_id'           => 'localId',
            'created_at'         => 'createdAt',
            'updated_at'         => 'updatedAt',
            'deleted_at'         => 'deletedAt',
            'sync_at'            => 'syncAt',
        ];

        foreach ($map as $from => $to) {
            if (array_key_exists($from, $item) && !array_key_exists($to, $item)) {
                $item[$to] = $item[$from];
            }
        }

        return Arr::only($item, [
            'id','code','remoteId','localId','stockOnHand','stockAllocated',
            'productVariantId','companyId','syncAt','version','account','isDirty',
            'createdBy','createdAt','updatedAt','deletedAt',
        ]);
    }

    /** Updatable columns on conflict (never touch createdAt). */
    protected function updatableColumns(): array
    {
        return [
            'remoteId','code','localId','stockOnHand','stockAllocated',
            'productVariantId','companyId','syncAt','version','account','isDirty',
            'createdBy','updatedAt','deletedAt',
        ];
    }

    /**
     * @OA\Get(
     *   path="/api/stock-levels",
     *   summary="List stock levels",
     *   tags={"StockLevels"},
     *   @OA\Parameter(name="q", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="search", in="query", description="Alias of q", @OA\Schema(type="string")),
     *   @OA\Parameter(name="isDirty", in="query", @OA\Schema(type="integer", enum={0,1})),
     *   @OA\Parameter(name="sort", in="query", description="e.g. updatedAt:desc or -updatedAt", @OA\Schema(type="string")),
     *   @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", minimum=1)),
     *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", minimum=1, maximum=200, default=20)),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function index(Request $request)
    {
        $query = StockLevel::query()->whereNull('deletedAt');

        $s = $request->query('q', $request->query('search'));
        if ($s) {
            $query->where(function ($q) use ($s) {
                $q->where('code', 'like', "%{$s}%")
                    ->orWhere('account', 'like', "%{$s}%");
            });
        }

        if ($request->has('isDirty')) {
            $query->where('isDirty', (bool) $request->integer('isDirty'));
        }

        $sortable = ['code','stockOnHand','stockAllocated','createdAt','updatedAt'];
        if ($sort = $request->query('sort')) {
            foreach (explode(',', $sort) as $part) {
                $part = trim($part);
                $dir = 'asc';
                $col = $part;
                if (str_contains($part, ':')) {
                    [$col, $dir] = explode(':', $part, 2);
                    $dir = strtolower(trim($dir)) === 'desc' ? 'desc' : 'asc';
                } elseif (str_starts_with($part, '-')) {
                    $dir = 'desc';
                    $col = ltrim($part, '-');
                }
                if (in_array($col, $sortable, true)) {
                    $query->orderBy($col, $dir);
                }
            }
        } else {
            $query->orderBy('createdAt', 'desc');
        }

        $perPage = max(1, min(200, (int) $request->integer('per_page', 20)));

        return StockLevelResource::collection(
            $query->paginate($perPage)->appends($request->query())
        );
    }

    /**
     * @OA\Post(
     *   path="/api/stock-levels",
     *   summary="Create a stock level",
     *   tags={"StockLevels"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StockLevelCreateRequest")),
     *   @OA\Response(response=201, description="Created")
     * )
     */
    public function store(StockLevelStoreRequest $request)
    {
        $data = $this->normalizeItem($request->validated());
        $now  = now();

        $data['id']        = $data['id']        ?? (string) Str::uuid();
        $data['createdAt'] = $data['createdAt'] ?? $now;
        $data['updatedAt'] = $data['updatedAt'] ?? $now;

        $model = StockLevel::create($data);

        return (new StockLevelResource($model))->response()->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *   path="/api/stock-levels/{id}",
     *   summary="Show a stock level",
     *   tags={"StockLevels"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function show(StockLevel $stockLevel)
    {
        return new StockLevelResource($stockLevel);
    }

    /**
     * @OA\Put(
     *   path="/api/stock-levels/{id}",
     *   summary="Update a stock level",
     *   tags={"StockLevels"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StockLevelUpdateRequest")),
     *   @OA\Response(response=200, description="OK")
     * )
     * @OA\Patch(
     *   path="/api/stock-levels/{id}",
     *   summary="Partially update a stock level",
     *   tags={"StockLevels"},
     *   @OA\RequestBody(required=false, @OA\JsonContent(ref="#/components/schemas/StockLevelUpdateRequest")),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function update(StockLevelUpdateRequest $request, StockLevel $stockLevel)
    {
        $data = $this->normalizeItem($request->validated());
        $data['updatedAt'] = now();

        $stockLevel->update($data);

        return new StockLevelResource($stockLevel->refresh());
    }

    /**
     * @OA\Delete(
     *   path="/api/stock-levels/{id}",
     *   summary="Soft-delete a stock level (single)",
     *   tags={"StockLevels"},
     *   @OA\Response(response=200, description="OK")
     * )
     * @OA\Delete(
     *   path="/api/stock-levels",
     *   summary="Soft-delete by ids (bulk)",
     *   tags={"StockLevels"},
     *   @OA\RequestBody(required=false, @OA\JsonContent(
     *     type="object",
     *     @OA\Property(property="ids", type="array", @OA\Items(type="string", format="uuid")),
     *     @OA\Property(property="items", type="array", @OA\Items(@OA\Property(property="id", type="string", format="uuid")))
     *   )),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function destroy(Request $request, ?string $id = null)
    {
        $now = now();

        // Single: /api/stock-levels/{id}
        if ($id) {
            $stockLevel = StockLevel::findOrFail($id);
            $stockLevel->update([
                'deletedAt' => $now,
                'updatedAt' => $now,
                'isDirty'   => true,
            ]);
            return new StockLevelResource($stockLevel->refresh());
        }

        // Bulk: ids[] or items[]. If none, no-op (200)
        $ids = collect($request->input('ids', []))
            ->merge(collect($request->input('items', []))->pluck('id'))
            ->filter()->unique()->values();

        if ($ids->isEmpty()) {
            return response()->json(['ok' => true], 200);
        }

        StockLevel::whereIn('id', $ids)->update([
            'deletedAt' => $now,
            'updatedAt' => $now,
            'isDirty'   => true,
        ]);

        return response()->json(['ok' => true], 200);
    }


    /**
     * @OA\Post(
     *   path="/api/stock-levels/restore",
     *   summary="Restore soft-deleted by IDs",
     *   tags={"StockLevels"},
     *   @OA\RequestBody(required=false, @OA\JsonContent(
     *     type="object",
     *     @OA\Property(property="ids", type="array", @OA\Items(type="string", format="uuid")),
     *     @OA\Property(property="items", type="array", @OA\Items(@OA\Property(property="id", type="string", format="uuid")))
     *   )),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function restore(Request $request)
    {
        $ids = collect($request->input('ids', []))
            ->merge(collect($request->input('items', []))->pluck('id'))
            ->filter()->unique()->values();

        $now = now();

        if ($ids->isEmpty()) {
            return response()->json(['ok' => true], 200);
        }

        StockLevel::whereIn('id', $ids)->update([
            'deletedAt' => null,
            'updatedAt' => $now,
            'isDirty'   => true,
        ]);

        // If a single id was restored, return the resource (helps tests assert deletedAt is null)
        if ($ids->count() === 1) {
            $model = StockLevel::find($ids->first());
            return new StockLevelResource($model);
        }

        return response()->json(['ok' => true], 200);
    }

    /**
     * @OA\Post(
     *   path="/api/stock-levels/bulk-upsert",
     *   summary="Bulk upsert stock levels",
     *   tags={"StockLevels"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(
     *     type="object", required={"items"},
     *     @OA\Property(property="items", type="array", @OA\Items(
     *       allOf={
     *         @OA\Schema(ref="#/components/schemas/StockLevelUpdateRequest"),
     *         @OA\Schema(
     *           description="type is case-insensitive",
     *           @OA\Property(property="id", type="string", format="uuid", nullable=true),
     *           @OA\Property(property="type", type="string", enum={"CREATE","UPDATE","UPSERT","DELETE","RESTORE"})
     *         )
     *       }
     *     ))
     *   )),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function bulkUpsert(StockLevelBulkUpsertRequest $request)
    {
        $items = $request->validated('items');

        $toDelete  = [];
        $toRestore = [];
        $toUpsert  = [];

        foreach ($items as $item) {
            $type = strtolower($item['type'] ?? 'upsert');
            $normalized = $this->normalizeItem($item);

            if (in_array($type, ['delete','restore'], true)) {
                if (!empty($item['id'])) {
                    $type === 'delete' ? $toDelete[] = $item['id'] : $toRestore[] = $item['id'];
                }
                continue;
            }

            // create/update/upsert need valid FKs (already validated), ensure timestamps
            $now = now();
            $normalized['id']        = $normalized['id'] ?? (string) Str::uuid();
            $normalized['createdAt'] = !empty($normalized['createdAt']) ? Carbon::parse($normalized['createdAt']) : $now;
            $normalized['updatedAt'] = $now;

            $toUpsert[] = $normalized;
        }

        DB::transaction(function () use ($toUpsert, $toDelete, $toRestore) {
            if (!empty($toUpsert)) {
                StockLevel::upsert($toUpsert, ['id'], $this->updatableColumns());
            }
            if (!empty($toDelete)) {
                $now = now();
                StockLevel::whereIn('id', $toDelete)->update([
                    'deletedAt' => $now,
                    'updatedAt' => $now,
                    'isDirty'   => true,
                ]);
            }
            if (!empty($toRestore)) {
                $now = now();
                StockLevel::whereIn('id', $toRestore)->update([
                    'deletedAt' => null,
                    'updatedAt' => $now,
                    'isDirty'   => true,
                ]);
            }
        });

        return response()->json([
            'upserted' => count($toUpsert),
            'deleted'  => count($toDelete),
            'restored' => count($toRestore),
            'status'   => 'ok',
        ]);
    }

    /**
     * @OA\Get(
     *   path="/api/stock-levels/export",
     *   summary="Export stock levels as CSV",
     *   tags={"StockLevels"},
     *   @OA\Response(response=200, description="CSV", content={@OA\MediaType(mediaType="text/csv")})
     * )
     */
    public function export(Request $request): StreamedResponse
    {
        $filename = 'stock_levels.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $columns = [
            'id','code','productVariantId','companyId',
            'stockOnHand','stockAllocated','createdAt','updatedAt',
        ];

        $rows = StockLevel::query()
            ->select($columns)
            ->whereNull('deletedAt')
            ->orderBy('createdAt', 'desc')
            ->get();

        return response()->stream(function () use ($rows, $columns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->id,
                    $r->code,
                    $r->productVariantId,
                    $r->companyId,
                    $r->stockOnHand,
                    $r->stockAllocated,
                    optional($r->createdAt)->format('Y-m-d H:i:s'),
                    optional($r->updatedAt)->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($out);
        }, 200, $headers);
    }

    /**
     * @OA\Post(
     *   path="/api/stock-levels/import",
     *   summary="Import stock levels from CSV or JSON items",
     *   tags={"StockLevels"},
     *   @OA\RequestBody(
     *     required=false,
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(
     *         type="object",
     *         @OA\Property(
     *           property="file",
     *           type="string",
     *           format="binary",
     *           description="CSV with headers"
     *         )
     *       )
     *     ),
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         type="object",
     *         @OA\Property(
     *           property="items",
     *           type="array",
     *           @OA\Items(ref="#/components/schemas/StockLevelUpdateRequest")
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(response=200, description="Imported"),
     *   @OA\Response(response=400, description="Invalid input")
     * )
     */

    public function import(StockLevelImportRequest $request)
    {
        $rows = collect();

        if ($request->hasFile('file')) {
            $path = $request->file('file')->getRealPath();
            if (($handle = fopen($path, 'r')) === false) {
                return response()->json(['message' => 'Unable to read file'], 400);
            }

            $header = fgetcsv($handle);
            if (!$header) {
                fclose($handle);
                return response()->json(['message' => 'Empty CSV'], 400);
            }

            $headerMap = [];
            foreach ($header as $i => $name) {
                $key = strtolower(trim($name));
                $normalized = match ($key) {
                    'product_variant_id' => 'productVariantId',
                    'company_id'         => 'companyId',
                    'stock_on_hand'      => 'stockOnHand',
                    'stock_allocated'    => 'stockAllocated',
                    'created_at'         => 'createdAt',
                    'updated_at'         => 'updatedAt',
                    'deleted_at'         => 'deletedAt',
                    default              => trim($name),
                };
                $headerMap[$i] = $normalized;
            }

            while (($data = fgetcsv($handle)) !== false) {
                $row = [];
                foreach ($data as $i => $value) {
                    $row[$headerMap[$i]] = $value === '' ? null : $value;
                }
                $rows->push($row);
            }
            fclose($handle);
        } else {
            $rows = collect($request->input('items', []));
        }

        if ($rows->isEmpty()) {
            return response()->json(['status' => 'ok', 'imported' => 0]);
        }

        $productTable = (new Product())->getTable();
        $companyTable = (new Company())->getTable();

        $now  = now();
        $data = $rows->map(function ($row) use ($now, $productTable, $companyTable) {
            $normalized = $this->normalizeItem($row);

            // Require both FKs; also skip orphaned references to avoid FK errors (SQLite strict)
            if (empty($normalized['productVariantId']) || empty($normalized['companyId'])) {
                return null;
            }

            $productExists = DB::table($productTable)->where('id', $normalized['productVariantId'])->exists();
            $companyExists = DB::table($companyTable)->where('id', $normalized['companyId'])->exists();
            if (!$productExists || !$companyExists) {
                return null; // skip invalid
            }

            $normalized['id']        = $normalized['id'] ?? (string) Str::uuid();
            $normalized['createdAt'] = !empty($normalized['createdAt']) ? Carbon::parse($normalized['createdAt']) : $now;
            $normalized['updatedAt'] = !empty($normalized['updatedAt']) ? Carbon::parse($normalized['updatedAt']) : $now;

            if (!empty($normalized['deletedAt'])) $normalized['deletedAt'] = Carbon::parse($normalized['deletedAt']);
            if (!empty($normalized['syncAt']))    $normalized['syncAt']    = Carbon::parse($normalized['syncAt']);

            return $normalized;
        })->filter()->values()->all();

        if (empty($data)) {
            return response()->json(['status' => 'ok', 'imported' => 0]);
        }

        StockLevel::upsert($data, ['id'], $this->updatableColumns());

        return response()->json(['status' => 'ok', 'imported' => count($data)]);
    }
}

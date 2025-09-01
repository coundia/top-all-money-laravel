<?php
// This controller provides CRUD, soft-delete, bulk upsert, CSV export/import for Product.

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Requests\Product\ProductStoreRequest;
use App\Http\Requests\Product\ProductUpdateRequest;
use App\Http\Requests\Product\ProductBulkUpsertRequest;
use App\Http\Requests\Product\ProductImportRequest;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/products",
     *   summary="List products",
     *   tags={"Products"},
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
        $sortCol = in_array($sortCol, ['updatedAt','createdAt','code','name','defaultPrice']) ? $sortCol : 'updatedAt';
        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        $query = Product::query()->whereNull('deletedAt');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('code', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }
        if ($isDirty !== null) $query->where('isDirty', $isDirty ? 1 : 0);

        $paginator = $query->orderBy($sortCol, $sortDir)->paginate($perPage);
        return ProductResource::collection($paginator);
    }

    /**
     * @OA\Post(
     *   path="/api/products",
     *   summary="Create a product",
     *   tags={"Products"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ProductCreateRequest")),
     *   @OA\Response(response=201, description="Created")
     * )
     */
    public function store(ProductStoreRequest $request)
    {
        $product = Product::create($request->validated());
        return (new ProductResource($product))->response()->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *   path="/api/products/{id}",
     *   summary="Show a product",
     *   tags={"Products"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function show(Product $product)
    {
        return new ProductResource($product);
    }

    /**
     * @OA\Put(
     *   path="/api/products/{id}",
     *   summary="Update a product",
     *   tags={"Products"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ProductUpdateRequest")),
     *   @OA\Response(response=200, description="OK")
     * )
     * @OA\Patch(
     *   path="/api/products/{id}",
     *   summary="Partially update a product",
     *   tags={"Products"},
     *   @OA\RequestBody(required=false, @OA\JsonContent(ref="#/components/schemas/ProductUpdateRequest")),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function update(ProductUpdateRequest $request, Product $product)
    {
        $product->update($request->validated());
        return new ProductResource($product);
    }

    /**
     * @OA\Delete(
     *   path="/api/products/{id}",
     *   summary="Soft delete",
     *   tags={"Products"},
     *   @OA\Response(response=200, description="Deleted")
     * )
     */
    public function destroy(Product $product)
    {
        $product->update(['deletedAt' => Carbon::now()->toISOString()]);
        return response()->json(['status' => 'deleted']);
    }

    /**
     * @OA\Post(
     *   path="/api/products/{id}/restore",
     *   summary="Restore a product",
     *   tags={"Products"},
     *   @OA\Response(response=200, description="Restored")
     * )
     */
    public function restore(Product $product)
    {
        $product->update(['deletedAt' => null]);
        return new ProductResource($product);
    }

    /**
     * @OA\Post(
     *   path="/api/products/bulk",
     *   summary="Bulk upsert products",
     *   tags={"Products"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(
     *     type="object", required={"items"},
     *     @OA\Property(property="items", type="array", @OA\Items(
     *       allOf={
     *         @OA\Schema(ref="#/components/schemas/ProductUpdateRequest"),
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
    public function bulkUpsert(ProductBulkUpsertRequest $request)
    {
        $now = now()->toISOString();
        $columns = [
            'id','remoteId','localId','code','account','name','description','barcode','unitId','categoryId',
            'defaultPrice','statuses','purchasePrice','createdAt','updatedAt','deletedAt','syncAt','createdBy','version','isDirty'
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

            $norm = [];
            foreach ($columns as $c) $norm[$c] = array_key_exists($c, $row) ? $row[$c] : null;
            foreach (['defaultPrice','purchasePrice','version'] as $intCol) {
                $norm[$intCol] = (int) ($norm[$intCol] ?? 0);
            }
            $norm['isDirty'] = (int) ($norm['isDirty'] ?? 1);

            $toUpsert[] = $norm;
        }

        if ($toUpsert) {
            $updateCols = array_values(array_diff($columns, ['id','createdAt']));
            Product::upsert($toUpsert, ['id'], $updateCols);
        }
        if ($toDelete) {
            Product::whereIn('id', $toDelete)->update(['deletedAt' => $now, 'updatedAt' => $now]);
        }

        $ids = array_merge(array_column($toUpsert, 'id'), $toDelete);
        $fresh = $ids ? Product::whereIn('id', $ids)->get() : collect();

        return ProductResource::collection($fresh);
    }

    /**
     * @OA\Get(
     *   path="/api/products/export",
     *   summary="Export products as CSV",
     *   tags={"Products"},
     *   @OA\Response(response=200, description="CSV", content={@OA\MediaType(mediaType="text/csv")})
     * )
     */
    public function export(Request $request): StreamedResponse
    {
        $q = (string) $request->query('q', '');
        $isDirty = $request->has('isDirty') ? (int) $request->query('isDirty') : null;

        $query = Product::query()->whereNull('deletedAt');
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('code', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }
        if ($isDirty !== null) $query->where('isDirty', $isDirty ? 1 : 0);

        $filename = 'products_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'id','remoteId','localId','code','account','name','description','barcode','unitId','categoryId',
                'defaultPrice','statuses','purchasePrice','createdAt','updatedAt','deletedAt','syncAt','createdBy','version','isDirty'
            ]);
            $query->orderBy('updatedAt','desc')->chunk(1000, function ($chunk) use ($out) {
                foreach ($chunk as $p) {
                    fputcsv($out, [
                        $p->id,$p->remoteId,$p->localId,$p->code,$p->account,$p->name,$p->description,$p->barcode,$p->unitId,$p->categoryId,
                        $p->defaultPrice,$p->statuses,$p->purchasePrice,$p->createdAt,$p->updatedAt,$p->deletedAt,$p->syncAt,$p->createdBy,$p->version,$p->isDirty
                    ]);
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @OA\Post(
     *   path="/api/products/import",
     *   summary="Import products from CSV",
     *   tags={"Products"},
     *   @OA\Response(response=200, description="Imported"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function import(ProductImportRequest $request)
    {
        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);
        $map = array_flip($header);
        $now = Carbon::now()->toISOString();
        $batch = [];

        $columns = [
            'id','remoteId','localId','code','account','name','description','barcode','unitId','categoryId',
            'defaultPrice','statuses','purchasePrice','createdAt','updatedAt','deletedAt','syncAt','createdBy','version','isDirty'
        ];

        while (($row = fgetcsv($handle)) !== false) {
            $val = fn($k) => array_key_exists($k,$map) ? ($row[$map[$k]] ?? null) : null;

            $item = [];
            foreach ($columns as $c) $item[$c] = $val($c);

            if (!$item['id']) $item['id'] = (string) Str::uuid();
            $item['createdAt'] = $item['createdAt'] ?: $now;
            $item['updatedAt'] = $now;
            $item['defaultPrice'] = (int) ($item['defaultPrice'] ?? 0);
            $item['purchasePrice'] = (int) ($item['purchasePrice'] ?? 0);
            $item['version'] = (int) ($item['version'] ?? 0);
            $item['isDirty'] = (int) ($item['isDirty'] ?? 1);

            $batch[] = $item;
            if (count($batch) >= 1000) {
                Product::upsert($batch, ['id'], array_diff($columns, ['id','createdAt']));
                $batch = [];
            }
        }
        if ($batch) Product::upsert($batch, ['id'], array_diff($columns, ['id','createdAt']));
        fclose($handle);

        return response()->json(['status' => 'imported']);
    }
}

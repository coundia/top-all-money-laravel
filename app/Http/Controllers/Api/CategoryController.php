<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Requests\Category\CategoryStoreRequest;
use App\Http\Requests\Category\CategoryUpdateRequest;
use App\Http\Requests\Category\CategoryBulkUpsertRequest;
use App\Http\Requests\Category\CategoryImportRequest;

class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/categories",
     *   summary="Lister les catégories",
     *   tags={"Categories"},
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
        $sortCol = in_array($sortCol, ['updatedAt','createdAt','code','typeEntry']) ? $sortCol : 'updatedAt';
        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        $query = Category::query()->whereNull('deletedAt');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('code', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }
        if ($isDirty !== null) $query->where('isDirty', $isDirty ? 1 : 0);

        $paginator = $query->orderBy($sortCol, $sortDir)->paginate($perPage);
        return CategoryResource::collection($paginator);
    }

    /**
     * @OA\Post(
     *   path="/api/categories",
     *   summary="Créer une catégorie",
     *   tags={"Categories"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/CategoryCreateRequest")),
     *   @OA\Response(response=201, description="Créé")
     * )
     */
    public function store(CategoryStoreRequest $request)
    {
        $category = Category::create($request->validated());
        return (new CategoryResource($category))->response()->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *   path="/api/categories/{id}",
     *   summary="Afficher une catégorie",
     *   tags={"Categories"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    /**
     * @OA\Put(
     *   path="/api/categories/{id}",
     *   summary="Mettre à jour une catégorie",
     *   tags={"Categories"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/CategoryUpdateRequest")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=404, description="Not found")
     * )
     *
     * @OA\Patch(
     *   path="/api/categories/{id}",
     *   summary="Modifier partiellement une catégorie",
     *   tags={"Categories"},
     *   @OA\RequestBody(required=false, @OA\JsonContent(ref="#/components/schemas/CategoryUpdateRequest")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function update(CategoryUpdateRequest $request, Category $category)
    {
        $category->update($request->validated());
        return new CategoryResource($category);
    }

    /**
     * @OA\Delete(
     *   path="/api/categories/{id}",
     *   summary="Soft delete",
     *   tags={"Categories"},
     *   @OA\Response(response=200, description="Supprimé")
     * )
     */
    public function destroy(Category $category)
    {
        $category->update(['deletedAt' => Carbon::now()->toISOString()]);
        return response()->json(['status' => 'deleted']);
    }

    /**
     * @OA\Post(
     *   path="/api/categories/{id}/restore",
     *   summary="Restaurer une catégorie",
     *   tags={"Categories"},
     *   @OA\Response(response=200, description="Restauré")
     * )
     */
    public function restore(Category $category)
    {
        $category->update(['deletedAt' => null]);
        return new CategoryResource($category);
    }

    /**
     * @OA\Post(
     *   path="/api/categories/bulk",
     *   summary="Upsert en masse",
     *   tags={"Categories"},
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
     *             @OA\Schema(ref="#/components/schemas/CategoryUpdateRequest"),
     *             @OA\Schema(
     *               @OA\Property(property="id", type="string", format="uuid", nullable=true),
     *               @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 enum={"CREATE","UPDATE","DELETE"}
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
    public function bulkUpsert(CategoryBulkUpsertRequest $request)
    {
        $now = now()->toISOString();
        $allColumns = [
            'id','remoteId','localId','code','description','typeEntry',
            'account','createdAt','updatedAt','deletedAt','syncAt',
            'isShared','createdBy','version','isDirty'
        ];

        $toUpsert = [];
        $toDeleteIds = [];

        foreach ($request->validated('items') as $row) {
            $type = strtoupper($row['type'] ?? 'CREATE');

            if ($type === 'DELETE') {
                $toDeleteIds[] = $row['id'];
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
            foreach ($allColumns as $col) $norm[$col] = array_key_exists($col, $row) ? $row[$col] : null;
            $norm['isDirty'] = $norm['isDirty'] ?? 1;
            $norm['version'] = $norm['version'] ?? 0;
            $norm['isShared'] = $norm['isShared'] ?? 0;

            $toUpsert[] = $norm;
        }

        if ($toUpsert) {
            $updateCols = array_values(array_diff($allColumns, ['id','createdAt']));
            Category::upsert($toUpsert, ['id'], $updateCols);
        }
        if ($toDeleteIds) {
            Category::whereIn('id', $toDeleteIds)->update(['deletedAt' => $now, 'updatedAt' => $now]);
        }

        $ids = array_merge(array_column($toUpsert, 'id'), $toDeleteIds);
        $fresh = $ids ? Category::whereIn('id', $ids)->get() : collect();

        return CategoryResource::collection($fresh);
    }

    /**
     * @OA\Get(
     *   path="/api/categories/export",
     *   summary="Exporter en CSV",
     *   tags={"Categories"},
     *   @OA\Response(response=200, description="CSV", content={
     *     @OA\MediaType(mediaType="text/csv")
     *   })
     * )
     */
    public function export(Request $request): StreamedResponse
    {
        $q = (string) $request->query('q', '');
        $isDirty = $request->has('isDirty') ? (int) $request->query('isDirty') : null;

        $query = Category::query()->whereNull('deletedAt');
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('code', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }
        if ($isDirty !== null) $query->where('isDirty', $isDirty ? 1 : 0);

        $filename = 'categories_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'id','remoteId','localId','code','description','typeEntry','account',
                'createdAt','updatedAt','deletedAt','syncAt','isShared','createdBy','version','isDirty'
            ]);
            $query->orderBy('updatedAt','desc')->chunk(1000, function ($chunk) use ($out) {
                foreach ($chunk as $c) {
                    fputcsv($out, [
                        $c->id,$c->remoteId,$c->localId,$c->code,$c->description,$c->typeEntry,$c->account,
                        $c->createdAt,$c->updatedAt,$c->deletedAt,$c->syncAt,$c->isShared,$c->createdBy,$c->version,$c->isDirty
                    ]);
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @OA\Post(
     *   path="/api/categories/import",
     *   summary="Importer un CSV",
     *   tags={"Categories"},
     *   @OA\RequestBody(
     *     required=true,
     *     content={
     *       @OA\MediaType(
     *         mediaType="multipart/form-data",
     *         @OA\Schema(
     *           type="object",
     *           required={"file"},
     *           @OA\Property(property="file", type="string", format="binary")
     *         )
     *       )
     *     }
     *   ),
     *   @OA\Response(response=200, description="Import terminé"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function import(CategoryImportRequest $request)
    {
        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);
        $map = array_flip($header);
        $now = Carbon::now()->toISOString();
        $batch = [];

        $columns = [
            'id','remoteId','localId','code','description','typeEntry','account',
            'createdAt','updatedAt','deletedAt','syncAt','isShared','createdBy','version','isDirty'
        ];

        while (($row = fgetcsv($handle)) !== false) {
            $val = fn($k) => array_key_exists($k,$map) ? ($row[$map[$k]] ?? null) : null;

            $item = [];
            foreach ($columns as $c) $item[$c] = $val($c);

            if (!$item['id']) $item['id'] = (string) Str::uuid();
            $item['createdAt'] = $item['createdAt'] ?: $now;
            $item['updatedAt'] = $now;
            $item['isDirty'] = $item['isDirty'] ?? 1;
            $item['version'] = $item['version'] ?? 0;
            $item['isShared'] = $item['isShared'] ?? 0;

            $batch[] = $item;
            if (count($batch) >= 1000) {
                Category::upsert($batch, ['id'], array_diff($columns, ['id','createdAt']));
                $batch = [];
            }
        }
        if ($batch) Category::upsert($batch, ['id'], array_diff($columns, ['id','createdAt']));
        fclose($handle);

        return response()->json(['status' => 'imported']);
    }
}

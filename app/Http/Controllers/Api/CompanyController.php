<?php
// This controller provides CRUD, soft-delete, bulk upsert, CSV export/import for Company.

namespace App\Http\Controllers\Api;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use App\Http\Requests\Company\CompanyStoreRequest;
use App\Http\Requests\Company\CompanyUpdateRequest;
use App\Http\Requests\Company\CompanyBulkUpsertRequest;
use App\Http\Requests\Company\CompanyImportRequest;

class CompanyController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/companies",
     *   summary="List companies",
     *   tags={"Companies"},
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
        $sortCol = in_array($sortCol, ['updatedAt','createdAt','code','name']) ? $sortCol : 'updatedAt';
        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        $query = Company::query()->whereNull('deletedAt');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('code', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }
        if ($isDirty !== null) $query->where('isDirty', $isDirty ? 1 : 0);

        $paginator = $query->orderBy($sortCol, $sortDir)->paginate($perPage);
        return CompanyResource::collection($paginator);
    }

    /**
     * @OA\Post(
     *   path="/api/companies",
     *   summary="Create a company",
     *   tags={"Companies"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/CompanyCreateRequest")),
     *   @OA\Response(response=201, description="Created")
     * )
     */
    public function store(CompanyStoreRequest $request)
    {
        $company = Company::create($request->validated());
        return (new CompanyResource($company))->response()->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *   path="/api/companies/{id}",
     *   summary="Show a company",
     *   tags={"Companies"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function show(Company $company)
    {
        return new CompanyResource($company);
    }

    /**
     * @OA\Put(
     *   path="/api/companies/{id}",
     *   summary="Update a company",
     *   tags={"Companies"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/CompanyUpdateRequest")),
     *   @OA\Response(response=200, description="OK")
     * )
     * @OA\Patch(
     *   path="/api/companies/{id}",
     *   summary="Partially update a company",
     *   tags={"Companies"},
     *   @OA\RequestBody(required=false, @OA\JsonContent(ref="#/components/schemas/CompanyUpdateRequest")),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function update(CompanyUpdateRequest $request, Company $company)
    {
        $company->update($request->validated());
        return new CompanyResource($company);
    }

    /**
     * @OA\Delete(
     *   path="/api/companies/{id}",
     *   summary="Soft delete",
     *   tags={"Companies"},
     *   @OA\Response(response=200, description="Deleted")
     * )
     */
    public function destroy(Company $company)
    {
        $company->update(['deletedAt' => Carbon::now()->toISOString()]);
        return response()->json(['status' => 'deleted']);
    }

    /**
     * @OA\Post(
     *   path="/api/companies/{id}/restore",
     *   summary="Restore a company",
     *   tags={"Companies"},
     *   @OA\Response(response=200, description="Restored")
     * )
     */
    public function restore(Company $company)
    {
        $company->update(['deletedAt' => null]);
        return new CompanyResource($company);
    }

    /**
     * @OA\Post(
     *   path="/api/companies/bulk",
     *   summary="Bulk upsert companies",
     *   tags={"Companies"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(
     *     type="object", required={"items"},
     *     @OA\Property(property="items", type="array", @OA\Items(
     *       allOf={
     *         @OA\Schema(ref="#/components/schemas/CompanyUpdateRequest"),
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
    public function bulkUpsert(CompanyBulkUpsertRequest $request)
    {
        $now = now()->toISOString();
        $columns = [
            'id','remoteId','localId','code','name','description','phone','email','website','taxId','currency',
            'addressLine1','addressLine2','city','region','country','postalCode','isDefault',
            'createdAt','updatedAt','deletedAt','syncAt','createdBy','version','isDirty'
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
            $norm['isDefault'] = (int) ($norm['isDefault'] ?? 0);
            $norm['version'] = (int) ($norm['version'] ?? 0);
            $norm['isDirty'] = (int) ($norm['isDirty'] ?? 1);

            $toUpsert[] = $norm;
        }

        if ($toUpsert) {
            $updateCols = array_values(array_diff($columns, ['id','createdAt']));
            Company::upsert($toUpsert, ['id'], $updateCols);
        }
        if ($toDelete) {
            Company::whereIn('id', $toDelete)->update(['deletedAt' => $now, 'updatedAt' => $now]);
        }

        $ids = array_merge(array_column($toUpsert, 'id'), $toDelete);
        $fresh = $ids ? Company::whereIn('id', $ids)->get() : collect();

        return CompanyResource::collection($fresh);
    }

    /**
     * @OA\Get(
     *   path="/api/companies/export",
     *   summary="Export companies as CSV",
     *   tags={"Companies"},
     *   @OA\Response(response=200, description="CSV", content={@OA\MediaType(mediaType="text/csv")})
     * )
     */
    public function export(Request $request): StreamedResponse
    {
        $q = (string) $request->query('q', '');
        $isDirty = $request->has('isDirty') ? (int) $request->query('isDirty') : null;

        $query = Company::query()->whereNull('deletedAt');
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('code', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }
        if ($isDirty !== null) $query->where('isDirty', $isDirty ? 1 : 0);

        $filename = 'companies_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'id','remoteId','localId','code','name','description','phone','email','website','taxId','currency',
                'addressLine1','addressLine2','city','region','country','postalCode','isDefault',
                'createdAt','updatedAt','deletedAt','syncAt','createdBy','version','isDirty'
            ]);
            $query->orderBy('updatedAt','desc')->chunk(1000, function ($chunk) use ($out) {
                foreach ($chunk as $c) {
                    fputcsv($out, [
                        $c->id,$c->remoteId,$c->localId,$c->code,$c->name,$c->description,$c->phone,$c->email,$c->website,$c->taxId,$c->currency,
                        $c->addressLine1,$c->addressLine2,$c->city,$c->region,$c->country,$c->postalCode,$c->isDefault,
                        $c->createdAt,$c->updatedAt,$c->deletedAt,$c->syncAt,$c->createdBy,$c->version,$c->isDirty
                    ]);
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @OA\Post(
     *   path="/api/companies/import",
     *   summary="Import companies from CSV",
     *   tags={"Companies"},
     *   @OA\Response(response=200, description="Imported"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function import(CompanyImportRequest $request)
    {
        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);
        $map = array_flip($header);
        $now = Carbon::now()->toISOString();
        $batch = [];

        $columns = [
            'id','remoteId','localId','code','name','description','phone','email','website','taxId','currency',
            'addressLine1','addressLine2','city','region','country','postalCode','isDefault',
            'createdAt','updatedAt','deletedAt','syncAt','createdBy','version','isDirty'
        ];

        while (($row = fgetcsv($handle)) !== false) {
            $val = fn($k) => array_key_exists($k,$map) ? ($row[$map[$k]] ?? null) : null;

            $item = [];
            foreach ($columns as $c) $item[$c] = $val($c);

            if (!$item['id']) $item['id'] = (string) Str::uuid();
            $item['createdAt'] = $item['createdAt'] ?: $now;
            $item['updatedAt'] = $now;
            $item['isDefault'] = (int) ($item['isDefault'] ?? 0);
            $item['version'] = (int) ($item['version'] ?? 0);
            $item['isDirty'] = (int) ($item['isDirty'] ?? 1);

            $batch[] = $item;
            if (count($batch) >= 1000) {
                Company::upsert($batch, ['id'], array_diff($columns, ['id','createdAt']));
                $batch = [];
            }
        }
        if ($batch) Company::upsert($batch, ['id'], array_diff($columns, ['id','createdAt']));
        fclose($handle);

        return response()->json(['status' => 'imported']);
    }
}

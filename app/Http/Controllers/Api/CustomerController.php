<?php
// This controller provides CRUD, soft-delete, bulk upsert, CSV export/import for Customer.

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Http\Requests\Customer\CustomerStoreRequest;
use App\Http\Requests\Customer\CustomerUpdateRequest;
use App\Http\Requests\Customer\CustomerBulkUpsertRequest;
use App\Http\Requests\Customer\CustomerImportRequest;

class CustomerController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/customers",
     *   summary="List customers",
     *   tags={"Customers"},
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
        $sortCol = in_array($sortCol, ['updatedAt','createdAt','code','fullName']) ? $sortCol : 'updatedAt';
        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        $query = Customer::query()->whereNull('deletedAt');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('code', 'like', "%{$q}%")
                    ->orWhere('fullName', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }
        if ($isDirty !== null) $query->where('isDirty', $isDirty ? 1 : 0);

        $paginator = $query->orderBy($sortCol, $sortDir)->paginate($perPage);
        return CustomerResource::collection($paginator);
    }

    /**
     * @OA\Post(
     *   path="/api/customers",
     *   summary="Create a customer",
     *   tags={"Customers"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/CustomerCreateRequest")),
     *   @OA\Response(response=201, description="Created")
     * )
     */
    public function store(CustomerStoreRequest $request)
    {
        $customer = Customer::create($request->validated());
        return (new CustomerResource($customer))->response()->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *   path="/api/customers/{id}",
     *   summary="Show a customer",
     *   tags={"Customers"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function show(Customer $customer)
    {
        return new CustomerResource($customer);
    }

    /**
     * @OA\Put(
     *   path="/api/customers/{id}",
     *   summary="Update a customer",
     *   tags={"Customers"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/CustomerUpdateRequest")),
     *   @OA\Response(response=200, description="OK")
     * )
     * @OA\Patch(
     *   path="/api/customers/{id}",
     *   summary="Partially update a customer",
     *   tags={"Customers"},
     *   @OA\RequestBody(required=false, @OA\JsonContent(ref="#/components/schemas/CustomerUpdateRequest")),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function update(CustomerUpdateRequest $request, Customer $customer)
    {
        $customer->update($request->validated());
        return new CustomerResource($customer);
    }

    /**
     * @OA\Delete(
     *   path="/api/customers/{id}",
     *   summary="Soft delete",
     *   tags={"Customers"},
     *   @OA\Response(response=200, description="Deleted")
     * )
     */
    public function destroy(Customer $customer)
    {
        $customer->update(['deletedAt' => Carbon::now()->toISOString()]);
        return response()->json(['status' => 'deleted']);
    }

    /**
     * @OA\Post(
     *   path="/api/customers/{id}/restore",
     *   summary="Restore a customer",
     *   tags={"Customers"},
     *   @OA\Response(response=200, description="Restored")
     * )
     */
    public function restore(Customer $customer)
    {
        $customer->update(['deletedAt' => null]);
        return new CustomerResource($customer);
    }

    /**
     * @OA\Post(
     *   path="/api/customers/bulk",
     *   summary="Bulk upsert customers",
     *   tags={"Customers"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(
     *     type="object", required={"items"},
     *     @OA\Property(property="items", type="array", @OA\Items(
     *       allOf={
     *         @OA\Schema(ref="#/components/schemas/CustomerUpdateRequest"),
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
    public function bulkUpsert(CustomerBulkUpsertRequest $request)
    {
        $now = now()->toISOString();
        $columns = [
            'id','remoteId','localId','code','firstName','lastName','fullName','balance','balanceDebt',
            'phone','email','notes','status','companyId','addressLine1','addressLine2','city','region','country','postalCode',
            'createdAt','updatedAt','deletedAt','syncAt','createdBy','version','isDirty','account'
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
            foreach (['balance','balanceDebt','version'] as $intCol) {
                $norm[$intCol] = (int) ($norm[$intCol] ?? 0);
            }
            $norm['isDirty'] = (int) ($norm['isDirty'] ?? 1);

            $toUpsert[] = $norm;
        }

        if ($toUpsert) {
            $updateCols = array_values(array_diff($columns, ['id','createdAt']));
            Customer::upsert($toUpsert, ['id'], $updateCols);
        }
        if ($toDelete) {
            Customer::whereIn('id', $toDelete)->update(['deletedAt' => $now, 'updatedAt' => $now]);
        }

        $ids = array_merge(array_column($toUpsert, 'id'), $toDelete);
        $fresh = $ids ? Customer::whereIn('id', $ids)->get() : collect();

        return CustomerResource::collection($fresh);
    }

    /**
     * @OA\Get(
     *   path="/api/customers/export",
     *   summary="Export customers as CSV",
     *   tags={"Customers"},
     *   @OA\Response(response=200, description="CSV", content={@OA\MediaType(mediaType="text/csv")})
     * )
     */
    public function export(Request $request): StreamedResponse
    {
        $q = (string) $request->query('q', '');
        $isDirty = $request->has('isDirty') ? (int) $request->query('isDirty') : null;

        $query = Customer::query()->whereNull('deletedAt');
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('code', 'like', "%{$q}%")
                    ->orWhere('fullName', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }
        if ($isDirty !== null) $query->where('isDirty', $isDirty ? 1 : 0);

        $filename = 'customers_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'id','remoteId','localId','code','firstName','lastName','fullName','balance','balanceDebt',
                'phone','email','notes','status','companyId','addressLine1','addressLine2','city','region','country','postalCode',
                'createdAt','updatedAt','deletedAt','syncAt','createdBy','version','isDirty','account'
            ]);
            $query->orderBy('updatedAt','desc')->chunk(1000, function ($chunk) use ($out) {
                foreach ($chunk as $c) {
                    fputcsv($out, [
                        $c->id,$c->remoteId,$c->localId,$c->code,$c->firstName,$c->lastName,$c->fullName,$c->balance,$c->balanceDebt,
                        $c->phone,$c->email,$c->notes,$c->status,$c->companyId,$c->addressLine1,$c->addressLine2,$c->city,$c->region,$c->country,$c->postalCode,
                        $c->createdAt,$c->updatedAt,$c->deletedAt,$c->syncAt,$c->createdBy,$c->version,$c->isDirty,$c->account
                    ]);
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @OA\Post(
     *   path="/api/customers/import",
     *   summary="Import customers from CSV",
     *   tags={"Customers"},
     *   @OA\Response(response=200, description="Imported"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function import(CustomerImportRequest $request)
    {
        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);
        $map = array_flip($header);
        $now = Carbon::now()->toISOString();
        $batch = [];

        $columns = [
            'id','remoteId','localId','code','firstName','lastName','fullName','balance','balanceDebt',
            'phone','email','notes','status','companyId','addressLine1','addressLine2','city','region','country','postalCode',
            'createdAt','updatedAt','deletedAt','syncAt','createdBy','version','isDirty','account'
        ];

        while (($row = fgetcsv($handle)) !== false) {
            $val = fn($k) => array_key_exists($k,$map) ? ($row[$map[$k]] ?? null) : null;

            $item = [];
            foreach ($columns as $c) $item[$c] = $val($c);

            if (!$item['id']) $item['id'] = (string) Str::uuid();
            $item['createdAt'] = $item['createdAt'] ?: $now;
            $item['updatedAt'] = $now;
            foreach (['balance','balanceDebt','version'] as $intCol) {
                $item[$intCol] = (int) ($item[$intCol] ?? 0);
            }
            $item['isDirty'] = (int) ($item['isDirty'] ?? 1);

            $batch[] = $item;
            if (count($batch) >= 1000) {
                Customer::upsert($batch, ['id'], array_diff($columns, ['id','createdAt']));
                $batch = [];
            }
        }
        if ($batch) Customer::upsert($batch, ['id'], array_diff($columns, ['id','createdAt']));
        fclose($handle);

        return response()->json(['status' => 'imported']);
    }
}

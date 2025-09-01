<?php
// This controller provides CRUD, soft-delete, bulk upsert, CSV export/import for StockMovement.

namespace App\Http\Controllers\Api;

use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\StockMovementResource;
use App\Http\Requests\StockMovement\StockMovementStoreRequest;
use App\Http\Requests\StockMovement\StockMovementUpdateRequest;
use App\Http\Requests\StockMovement\StockMovementBulkUpsertRequest;
use App\Http\Requests\StockMovement\StockMovementImportRequest;

class StockMovementController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/stock-movements",
     *   summary="List stock movements",
     *   tags={"StockMovements"},
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
        $sortCol = in_array($sortCol, ['updatedAt','createdAt','quantity','type_stock_movement']) ? $sortCol : 'updatedAt';
        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        $query = StockMovement::query()->whereNull('deletedAt');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('code', 'like', "%{$q}%")
                    ->orWhere('type_stock_movement', 'like', "%{$q}%")
                    ->orWhere('productVariantId', 'like', "%{$q}%");
            });
        }
        if ($isDirty !== null) $query->where('isDirty', $isDirty ? 1 : 0);

        return StockMovementResource::collection(
            $query->orderBy($sortCol, $sortDir)->paginate($perPage)
        );
    }

    /**
     * @OA\Post(
     *   path="/api/stock-movements",
     *   summary="Create a stock movement",
     *   tags={"StockMovements"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StockMovementCreateRequest")),
     *   @OA\Response(response=201, description="Created")
     * )
     */
    public function store(StockMovementStoreRequest $request)
    {
        $row = StockMovement::create($request->validated());
        return (new StockMovementResource($row))->response()->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *   path="/api/stock-movements/{id}",
     *   summary="Show a stock movement",
     *   tags={"StockMovements"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function show(StockMovement $stockMovement)
    {
        return new StockMovementResource($stockMovement);
    }

    /**
     * @OA\Put(
     *   path="/api/stock-movements/{id}",
     *   summary="Update a stock movement",
     *   tags={"StockMovements"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StockMovementUpdateRequest")),
     *   @OA\Response(response=200, description="OK")
     * )
     * @OA\Patch(
     *   path="/api/stock-movements/{id}",
     *   summary="Partially update a stock movement",
     *   tags={"StockMovements"},
     *   @OA\RequestBody(required=false, @OA\JsonContent(ref="#/components/schemas/StockMovementUpdateRequest")),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function update(StockMovementUpdateRequest $request, StockMovement $stockMovement)
    {
        $stockMovement->update($request->validated());
        return new StockMovementResource($stockMovement);
    }

    /**
     * @OA\Delete(
     *   path="/api/stock-movements/{id}",
     *   summary="Soft delete",
     *   tags={"StockMovements"},
     *   @OA\Response(response=200, description="Deleted")
     * )
     */
    public function destroy(StockMovement $stockMovement)
    {
        $stockMovement->update(['deletedAt' => Carbon::now()->toISOString()]);
        return response()->json(['status' => 'deleted']);
    }

    /**
     * @OA\Post(
     *   path="/api/stock-movements/{id}/restore",
     *   summary="Restore a stock movement",
     *   tags={"StockMovements"},
     *   @OA\Response(response=200, description="Restored")
     * )
     */
    public function restore(StockMovement $stockMovement)
    {
        $stockMovement->update(['deletedAt' => null]);
        return new StockMovementResource($stockMovement);
    }

    /**
     * @OA\Post(
     *   path="/api/stock-movements/bulk",
     *   summary="Bulk upsert stock movements",
     *   tags={"StockMovements"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(
     *     type="object", required={"items"},
     *     @OA\Property(property="items", type="array", @OA\Items(
     *       allOf={
     *         @OA\Schema(ref="#/components/schemas/StockMovementUpdateRequest"),
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
    public function bulkUpsert(StockMovementBulkUpsertRequest $request)
    {
        $now = now()->toISOString();
        $cols = [
            'id','type_stock_movement','code','remoteId','localId','quantity','companyId','productVariantId',
            'orderLineId','discriminator','account','syncAt','version','isDirty','createdBy','createdAt','updatedAt','deletedAt'
        ];

        $up=[]; $del=[];
        foreach($request->validated('items') as $row){
            $t=strtoupper($row['type']??'CREATE');
            if($t==='DELETE'){ $del[]=$row['id']; continue; }

            if(empty($row['id'])){ $row['id']=(string)Str::uuid(); $row['createdAt']=$row['createdAt']??$now; }
            else { $row['createdAt']=$row['createdAt']??$now; }
            $row['updatedAt']=$now;

            $norm=[];
            foreach($cols as $c) $norm[$c]=array_key_exists($c,$row)?$row[$c]:null;
            $norm['quantity']=(int)($norm['quantity']??0);
            $norm['version']=(int)($norm['version']??0);
            $norm['isDirty']=(int)($norm['isDirty']??1);

            $up[]=$norm;
        }

        if($up) StockMovement::upsert($up,['id'],array_values(array_diff($cols,['id','createdAt'])));
        if($del) StockMovement::whereIn('id',$del)->update(['deletedAt'=>$now,'updatedAt'=>$now]);

        $ids=array_merge(array_column($up,'id'),$del);
        $fresh=$ids?StockMovement::whereIn('id',$ids)->get():collect();

        return StockMovementResource::collection($fresh);
    }

    /**
     * @OA\Get(
     *   path="/api/stock-movements/export",
     *   summary="Export stock movements as CSV",
     *   tags={"StockMovements"},
     *   @OA\Response(response=200, description="CSV", content={@OA\MediaType(mediaType="text/csv")})
     * )
     */
    public function export(Request $request): StreamedResponse
    {
        $q=(string)$request->query('q','');
        $isDirty=$request->has('isDirty')?(int)$request->query('isDirty'):null;

        $query=StockMovement::query()->whereNull('deletedAt');
        if($q!==''){
            $query->where(function($s)use($q){
                $s->where('code','like',"%{$q}%")
                    ->orWhere('type_stock_movement','like',"%{$q}%")
                    ->orWhere('productVariantId','like',"%{$q}%");
            });
        }
        if($isDirty!==null) $query->where('isDirty',$isDirty?1:0);

        $filename='stock_movements_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function()use($query){
            $out=fopen('php://output','w');
            fputcsv($out,[
                'id','type_stock_movement','code','remoteId','localId','quantity','companyId','productVariantId',
                'orderLineId','discriminator','account','syncAt','version','isDirty','createdBy','createdAt','updatedAt','deletedAt'
            ]);
            $query->orderBy('updatedAt','desc')->chunk(1000,function($chunk)use($out){
                foreach($chunk as $r){
                    fputcsv($out,[
                        $r->id,$r->type_stock_movement,$r->code,$r->remoteId,$r->localId,$r->quantity,$r->companyId,$r->productVariantId,
                        $r->orderLineId,$r->discriminator,$r->account,$r->syncAt,$r->version,$r->isDirty,$r->createdBy,$r->createdAt,$r->updatedAt,$r->deletedAt
                    ]);
                }
            });
            fclose($out);
        },$filename,['Content-Type'=>'text/csv; charset=UTF-8']);
    }

    /**
     * @OA\Post(
     *   path="/api/stock-movements/import",
     *   summary="Import stock movements from CSV",
     *   tags={"StockMovements"},
     *   @OA\Response(response=200, description="Imported"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function import(StockMovementImportRequest $request)
    {
        $file=$request->file('file');
        $h=fopen($file->getRealPath(),'r');
        $header=fgetcsv($h);
        $map=array_flip($header);
        $now=Carbon::now()->toISOString();
        $batch=[];

        $cols=[
            'id','type_stock_movement','code','remoteId','localId','quantity','companyId','productVariantId',
            'orderLineId','discriminator','account','syncAt','version','isDirty','createdBy','createdAt','updatedAt','deletedAt'
        ];

        while(($row=fgetcsv($h))!==false){
            $val=fn($k)=>array_key_exists($k,$map)?($row[$map[$k]]??null):null;
            $item=[];
            foreach($cols as $c) $item[$c]=$val($c);

            if(!$item['id']) $item['id']=(string)Str::uuid();
            $item['createdAt']=$item['createdAt']?:$now;
            $item['updatedAt']=$now;
            $item['quantity']=(int)($item['quantity']??0);
            $item['version']=(int)($item['version']??0);
            $item['isDirty']=(int)($item['isDirty']??1);

            $batch[]=$item;
            if(count($batch)>=1000){ StockMovement::upsert($batch,['id'],array_diff($cols,['id','createdAt'])); $batch=[]; }
        }
        if($batch) StockMovement::upsert($batch,['id'],array_diff($cols,['id','createdAt']));
        fclose($h);

        return response()->json(['status'=>'imported']);
    }
}

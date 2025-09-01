<?php
// This controller provides CRUD, soft-delete, bulk upsert, CSV export/import for Debt.

namespace App\Http\Controllers\Api;

use App\Models\Debt;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\DebtResource;
use App\Http\Requests\Debt\DebtStoreRequest;
use App\Http\Requests\Debt\DebtUpdateRequest;
use App\Http\Requests\Debt\DebtBulkUpsertRequest;
use App\Http\Requests\Debt\DebtImportRequest;

class DebtController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/debts",
     *   summary="List debts",
     *   tags={"Debts"},
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
        $sortCol = in_array($sortCol, ['updatedAt','createdAt','code','dueDate','balance','balanceDebt']) ? $sortCol : 'updatedAt';
        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        $query = Debt::query()->whereNull('deletedAt');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('code', 'like', "%{$q}%")
                    ->orWhere('notes', 'like', "%{$q}%");
            });
        }
        if ($isDirty !== null) $query->where('isDirty', $isDirty ? 1 : 0);

        return DebtResource::collection($query->orderBy($sortCol, $sortDir)->paginate($perPage));
    }

    /**
     * @OA\Post(
     *   path="/api/debts",
     *   summary="Create a debt",
     *   tags={"Debts"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/DebtCreateRequest")),
     *   @OA\Response(response=201, description="Created")
     * )
     */
    public function store(DebtStoreRequest $request)
    {
        $row = Debt::create($request->validated());
        return (new DebtResource($row))->response()->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *   path="/api/debts/{id}",
     *   summary="Show a debt",
     *   tags={"Debts"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function show(Debt $debt)
    {
        return new DebtResource($debt);
    }

    /**
     * @OA\Put(
     *   path="/api/debts/{id}",
     *   summary="Update a debt",
     *   tags={"Debts"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/DebtUpdateRequest")),
     *   @OA\Response(response=200, description="OK")
     * )
     * @OA\Patch(
     *   path="/api/debts/{id}",
     *   summary="Partially update a debt",
     *   tags={"Debts"},
     *   @OA\RequestBody(required=false, @OA\JsonContent(ref="#/components/schemas/DebtUpdateRequest")),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function update(DebtUpdateRequest $request, Debt $debt)
    {
        $debt->update($request->validated());
        return new DebtResource($debt);
    }

    /**
     * @OA\Delete(
     *   path="/api/debts/{id}",
     *   summary="Soft delete",
     *   tags={"Debts"},
     *   @OA\Response(response=200, description="Deleted")
     * )
     */
    public function destroy(Debt $debt)
    {
        $debt->update(['deletedAt' => Carbon::now()->toISOString()]);
        return response()->json(['status' => 'deleted']);
    }

    /**
     * @OA\Post(
     *   path="/api/debts/{id}/restore",
     *   summary="Restore a debt",
     *   tags={"Debts"},
     *   @OA\Response(response=200, description="Restored")
     * )
     */
    public function restore(Debt $debt)
    {
        $debt->update(['deletedAt' => null]);
        return new DebtResource($debt);
    }

    /**
     * @OA\Post(
     *   path="/api/debts/bulk",
     *   summary="Bulk upsert debts",
     *   tags={"Debts"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(
     *     type="object", required={"items"},
     *     @OA\Property(property="items", type="array", @OA\Items(
     *       allOf={
     *         @OA\Schema(ref="#/components/schemas/DebtUpdateRequest"),
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
    public function bulkUpsert(DebtBulkUpsertRequest $request)
    {
        $now = now()->toISOString();
        $cols = [
            'id','remoteId','localId','code','notes','balance','balanceDebt','dueDate','statuses','account','customerId',
            'createdAt','updatedAt','deletedAt','syncAt','createdBy','version','isDirty'
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
            $norm['balance']=(int)($norm['balance']??0);
            $norm['balanceDebt']=(int)($norm['balanceDebt']??0);
            $norm['version']=(int)($norm['version']??0);
            $norm['isDirty']=(int)($norm['isDirty']??1);

            $up[]=$norm;
        }
        if($up) Debt::upsert($up,['id'],array_values(array_diff($cols,['id','createdAt'])));
        if($del) Debt::whereIn('id',$del)->update(['deletedAt'=>$now,'updatedAt'=>$now]);

        $ids=array_merge(array_column($up,'id'),$del);
        $fresh=$ids?Debt::whereIn('id',$ids)->get():collect();
        return DebtResource::collection($fresh);
    }

    /**
     * @OA\Get(
     *   path="/api/debts/export",
     *   summary="Export debts as CSV",
     *   tags={"Debts"},
     *   @OA\Response(response=200, description="CSV", content={@OA\MediaType(mediaType="text/csv")})
     * )
     */
    public function export(Request $request): StreamedResponse
    {
        $q=(string)$request->query('q','');
        $isDirty=$request->has('isDirty')?(int)$request->query('isDirty'):null;

        $query=Debt::query()->whereNull('deletedAt');
        if($q!==''){
            $query->where(function($s)use($q){ $s->where('code','like',"%{$q}%")->orWhere('notes','like',"%{$q}%"); });
        }
        if($isDirty!==null) $query->where('isDirty',$isDirty?1:0);

        $filename='debts_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function()use($query){
            $out=fopen('php://output','w');
            fputcsv($out,[
                'id','remoteId','localId','code','notes','balance','balanceDebt','dueDate','statuses','account','customerId',
                'createdAt','updatedAt','deletedAt','syncAt','createdBy','version','isDirty'
            ]);
            $query->orderBy('updatedAt','desc')->chunk(1000,function($chunk)use($out){
                foreach($chunk as $r){
                    fputcsv($out,[
                        $r->id,$r->remoteId,$r->localId,$r->code,$r->notes,$r->balance,$r->balanceDebt,$r->dueDate,$r->statuses,$r->account,$r->customerId,
                        $r->createdAt,$r->updatedAt,$r->deletedAt,$r->syncAt,$r->createdBy,$r->version,$r->isDirty
                    ]);
                }
            });
            fclose($out);
        },$filename,['Content-Type'=>'text/csv; charset=UTF-8']);
    }

    /**
     * @OA\Post(
     *   path="/api/debts/import",
     *   summary="Import debts from CSV",
     *   tags={"Debts"},
     *   @OA\Response(response=200, description="Imported"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function import(DebtImportRequest $request)
    {
        $file=$request->file('file');
        $h=fopen($file->getRealPath(),'r');
        $header=fgetcsv($h);
        $map=array_flip($header);
        $now=Carbon::now()->toISOString();
        $batch=[];
        $cols=[
            'id','remoteId','localId','code','notes','balance','balanceDebt','dueDate','statuses','account','customerId',
            'createdAt','updatedAt','deletedAt','syncAt','createdBy','version','isDirty'
        ];
        while(($row=fgetcsv($h))!==false){
            $val=fn($k)=>array_key_exists($k,$map)?($row[$map[$k]]??null):null;
            $item=[];
            foreach($cols as $c) $item[$c]=$val($c);
            if(!$item['id']) $item['id']=(string)Str::uuid();
            $item['createdAt']=$item['createdAt']?:$now;
            $item['updatedAt']=$now;
            $item['balance']=(int)($item['balance']??0);
            $item['balanceDebt']=(int)($item['balanceDebt']??0);
            $item['version']=(int)($item['version']??0);
            $item['isDirty']=(int)($item['isDirty']??1);

            $batch[]=$item;
            if(count($batch)>=1000){ Debt::upsert($batch,['id'],array_diff($cols,['id','createdAt'])); $batch=[]; }
        }
        if($batch) Debt::upsert($batch,['id'],array_diff($cols,['id','createdAt']));
        fclose($h);

        return response()->json(['status'=>'imported']);
    }
}

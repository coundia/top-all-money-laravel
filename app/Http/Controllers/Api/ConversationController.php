<?php

namespace App\Http\Controllers\Api;

use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\ConversationResource;
use App\Http\Requests\Conversation\ConversationStoreRequest;
use App\Http\Requests\Conversation\ConversationUpdateRequest;
use App\Http\Requests\Conversation\ConversationBulkUpsertRequest;
use App\Http\Requests\Conversation\ConversationImportRequest;

/**
 * ConversationController manages CRUD, bulk upsert, and CSV import/export for conversations.
 */
class ConversationController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/conversations",
     *   summary="List conversations",
     *   tags={"Conversations"},
     *   @OA\Parameter(name="q", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="sort", in="query", @OA\Schema(type="string", example="updated_at:desc")),
     *   @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", minimum=1)),
     *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", minimum=1, maximum=200, default=20)),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function index(Request $request)
    {
        $q = (string) $request->query('q', '');
        $perPage = min(max((int) $request->query('per_page', 20), 1), 200);
        $sort = (string) $request->query('sort', 'updated_at:desc');
        [$sortCol, $sortDir] = array_pad(explode(':', $sort, 2), 2, 'desc');
        $allowedSort = ['updated_at', 'created_at', 'title'];
        $sortCol = in_array($sortCol, $allowedSort, true) ? $sortCol : 'updated_at';
        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        $query = Conversation::query();

        if ($q !== '') {
            $query->where('title', 'like', "%{$q}%");
        }

        $paginator = $query->orderBy($sortCol, $sortDir)->paginate($perPage);
        return ConversationResource::collection($paginator);
    }

    /**
     * @OA\Post(
     *   path="/api/conversations",
     *   summary="Create conversation",
     *   tags={"Conversations"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ConversationCreateRequest")),
     *   @OA\Response(response=201, description="Created")
     * )
     */
    public function store(ConversationStoreRequest $request)
    {
        $row = Conversation::create($request->validated());
        return (new ConversationResource($row))->response()->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *   path="/api/conversations/{id}",
     *   summary="Show conversation",
     *   tags={"Conversations"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(Conversation $conversation)
    {
        return new ConversationResource($conversation);
    }

    /**
     * @OA\Put(
     *   path="/api/conversations/{id}",
     *   summary="Update conversation",
     *   tags={"Conversations"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ConversationUpdateRequest")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=404, description="Not found")
     * )
     * @OA\Patch(
     *   path="/api/conversations/{id}",
     *   summary="Partially update conversation",
     *   tags={"Conversations"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\RequestBody(required=false, @OA\JsonContent(ref="#/components/schemas/ConversationUpdateRequest")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function update(ConversationUpdateRequest $request, Conversation $conversation)
    {
        $conversation->update($request->validated());
        $conversation->refresh();

        return new ConversationResource($conversation);
    }

    /**
     * @OA\Delete(
     *   path="/api/conversations/{id}",
     *   summary="Hard delete conversation",
     *   tags={"Conversations"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="Deleted")
     * )
     */
    public function destroy(Conversation $conversation)
    {
        $conversation->delete();
        return response()->json(['status' => 'deleted']);
    }

    /**
     * @OA\Post(
     *   path="/api/conversations/bulk",
     *   summary="Bulk upsert conversations",
     *   tags={"Conversations"},
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
     *             @OA\Schema(ref="#/components/schemas/ConversationUpdateRequest"),
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
    public function bulkUpsert(\App\Http\Requests\Conversation\ConversationBulkUpsertRequest $request)
    {
        $now = now();

        $columns = ['id','title','createdBy','created_at','updated_at'];

        $toUpsert = [];
        $toDeleteIds = [];

        foreach ($request->validated('items') as $row) {
            $type = strtoupper($row['type'] ?? 'CREATE');

            if ($type === 'DELETE') {
                $toDeleteIds[] = $row['id']; // validé par la Request
                continue;
            }

            if (empty($row['id'])) {
                $row['id'] = (string) \Illuminate\Support\Str::uuid();
                $row['created_at'] = $row['created_at'] ?? $now;
            } else {
                $row['created_at'] = $row['created_at'] ?? $now;
            }
            $row['updated_at'] = $now;

            $norm = [];
            foreach ($columns as $c) {
                $norm[$c] = array_key_exists($c, $row) ? $row[$c] : null;
            }

            $toUpsert[] = $norm;
        }

        // Upsert (CREATE/UPDATE)
        if ($toUpsert) {
            $updateCols = array_values(array_diff($columns, ['id','created_at']));
            \App\Models\Conversation::upsert($toUpsert, ['id'], $updateCols);
        }

        // Hard delete pour DELETE
        if ($toDeleteIds) {
            \App\Models\Conversation::whereIn('id', $toDeleteIds)->delete();
        }

        // Récupérer les éléments upsertés
        $upsertIds = array_column($toUpsert, 'id');
        $fresh = $upsertIds ? \App\Models\Conversation::whereIn('id', $upsertIds)->get() : collect();

        // Transformer en resource array
        $upsertedData = \App\Http\Resources\ConversationResource::collection($fresh)->resolve();

        // Ajouter des placeholders pour les deletes afin de garder le même nombre d'items retournés
        $deletedPlaceholders = array_map(function ($id) {
            return [
                'id'         => $id,
                'title'      => null,
                'created_at' => null,
                'updated_at' => null,
                '_deleted'   => true, // petit indicateur facultatif
            ];
        }, $toDeleteIds);

        // Fusionner et renvoyer
        $data = array_values(array_merge($upsertedData, $deletedPlaceholders));

        return response()->json(['data' => $data]);
    }


    /**
     * @OA\Get(
     *   path="/api/conversations/export",
     *   summary="Export conversations to CSV",
     *   tags={"Conversations"},
     *   @OA\Response(response=200, description="CSV", content={@OA\MediaType(mediaType="text/csv")})
     * )
     */
    public function export(Request $request): StreamedResponse
    {
        $q = (string) $request->query('q', '');

        $query = Conversation::query();
        if ($q !== '') {
            $query->where('title', 'like', "%{$q}%");
        }

        // Match test expectation: id,title,createdBy,createdAt,updatedAt
        $headers = ['id','title','createdBy','createdAt','updatedAt'];
        $filename = 'conversations_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query, $headers) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);

            $query->orderBy('updated_at','desc')->chunk(1000, function ($chunk) use ($out) {
                foreach ($chunk as $row) {
                    fputcsv($out, [
                        $row->id,
                        $row->title,
                        $row->createdBy ?? null,
                        optional($row->created_at)->format('Y-m-d H:i:s'),
                        optional($row->updated_at)->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @OA\Post(
     *   path="/api/conversations/import",
     *   summary="Import conversations from CSV",
     *   tags={"Conversations"},
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
    public function import(ConversationImportRequest $request)
    {
        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);
        $map = array_flip($header);
        $now = now();

        $columns = ['id','title','createdBy','created_at','updated_at'];

        $batch = [];
        while (($row = fgetcsv($handle)) !== false) {
            $val = fn($k) => array_key_exists($k,$map) ? ($row[$map[$k]] ?? null) : null;

            // Accept both camel and snake for timestamps and createdBy
            $createdAt = $val('createdAt') ?? $val('created_at');
            $updatedAt = $val('updatedAt') ?? $val('updated_at');
            $createdBy = $val('createdBy') ?? null;

            $item = [
                'id'          => $val('id') ?: (string) Str::uuid(),
                'title'       => $val('title'),
                'createdBy'   => $createdBy,
                'created_at'  => $createdAt ?: $now,
                'updated_at'  => $updatedAt ?: $now,
            ];

            $batch[] = $item;

            if (count($batch) >= 1000) {
                Conversation::upsert($batch, ['id'], array_diff($columns, ['id','created_at']));
                $batch = [];
            }
        }
        if ($batch) {
            Conversation::upsert($batch, ['id'], array_diff($columns, ['id','created_at']));
        }
        fclose($handle);

        return response()->json(['status' => 'imported']);
    }
}

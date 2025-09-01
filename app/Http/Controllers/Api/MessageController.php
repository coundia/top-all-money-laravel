<?php

namespace App\Http\Controllers\Api;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Http\Requests\Message\MessageStoreRequest;
use App\Http\Requests\Message\MessageUpdateRequest;
use App\Http\Requests\Message\MessageBulkUpsertRequest;
use App\Http\Requests\Message\MessageImportRequest;

/**
 * MessageController handles CRUD and import/export for messages
 */
class MessageController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/messages",
     *   summary="List messages",
     *   tags={"Messages"},
     *   @OA\Parameter(name="q", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="isDirty", in="query", @OA\Schema(type="integer", enum={0,1})),
     *   @OA\Parameter(name="sort", in="query", @OA\Schema(type="string", example="updated_at:desc")),
     *   @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", minimum=1, maximum=200, default=20)),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function index(Request $request)
    {
        $q = (string) $request->query('q', '');
        $isDirty = $request->has('isDirty') ? (int) $request->query('isDirty') : null;
        $perPage = min(max((int) $request->query('per_page', 20), 1), 200);
        $sort = (string) $request->query('sort', 'updated_at:desc');
        [$sortCol, $sortDir] = array_pad(explode(':', $sort, 2), 2, 'desc');
        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        $query = Message::query();

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('body', 'like', "%{$q}%")
                    ->orWhere('sender', 'like', "%{$q}%");
            });
        }
        if ($isDirty !== null) $query->where('isDirty', $isDirty ? 1 : 0);

        return MessageResource::collection(
            $query->orderBy($sortCol, $sortDir)->paginate($perPage)
        );
    }

    /**
     * @OA\Post(
     *   path="/api/messages",
     *   summary="Create message",
     *   tags={"Messages"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/MessageCreateRequest")),
     *   @OA\Response(response=201, description="Created")
     * )
     */
    public function store(MessageStoreRequest $request)
    {
        $msg = Message::create($request->validated());
        return (new MessageResource($msg))->response()->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *   path="/api/messages/{id}",
     *   summary="Show message",
     *   tags={"Messages"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function show(Message $message)
    {
        return new MessageResource($message);
    }

    /**
     * @OA\Put(
     *   path="/api/messages/{id}",
     *   summary="Update message",
     *   tags={"Messages"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/MessageUpdateRequest")),
     *   @OA\Response(response=200, description="OK")
     * )
     * @OA\Patch(
     *   path="/api/messages/{id}",
     *   summary="Partially update message",
     *   tags={"Messages"},
     *   @OA\RequestBody(required=false, @OA\JsonContent(ref="#/components/schemas/MessageUpdateRequest")),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function update(MessageUpdateRequest $request, Message $message)
    {
        $message->update($request->validated());
        return new MessageResource($message);
    }

    /**
     * @OA\Delete(
     *   path="/api/messages/{id}",
     *   summary="Delete message",
     *   tags={"Messages"},
     *   @OA\Response(response=200, description="Deleted")
     * )
     */
    public function destroy(Message $message)
    {
        $message->delete();
        return response()->json(['status' => 'deleted']);
    }
}

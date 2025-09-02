<?php

namespace App\Support;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

trait HandlesApiExceptions
{
    use ApiResponse;

    protected function safe(\Closure $callback): mixed
    {
        try {
            return $callback();
        } catch (ValidationException $e) {
            return $this->error('Validation error', 422, $e->errors());
        } catch (AuthenticationException $e) {
            return $this->error('Unauthenticated', 401);
        } catch (AuthorizationException $e) {
            return $this->error('Forbidden', 403);
        } catch (ModelNotFoundException|NotFoundHttpException $e) {
            return $this->error('Not found', 404);
        } catch (MethodNotAllowedHttpException $e) {
            return $this->error('Method not allowed', 405);
        } catch (ThrottleRequestsException $e) {
            return $this->error('Too Many Requests', 429);
        } catch (QueryException $e) {
            return $this->error('Database error', 500);
        } catch (Throwable $e) {
            if (config('app.debug')) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'exception' => class_basename($e),
                    'trace' => collect($e->getTrace())->take(3),
                ], 500);
            }
            return $this->error('Server error', 500);
        }
    }
}

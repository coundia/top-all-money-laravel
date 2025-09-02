<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected array $defaultJsonHeaders = [
        'Accept'       => 'application/json',
        'Content-Type' => 'application/json',
    ];

    protected bool $seedPermissions = false;

    protected function setUp(): void
    {
        parent::setUp();

        // En-tête JSON par défaut
        foreach ($this->defaultJsonHeaders as $k => $v) {
            $this->withHeader($k, $v);
        }

        if ($this->seedPermissions) {
            $this->seed(\Database\Seeders\PermissionSeeder::class);
        }
    }

    protected function jsonGet(string $uri, array $params = [], array $headers = [])
    {
        $q = $params ? ('?'.http_build_query($params)) : '';
        return $this->getJson($uri.$q, $headers);
    }

    protected function jsonPost(string $uri, array $data = [], array $headers = [])
    {
        return $this->postJson($uri, $data, $headers);
    }

    protected function jsonPut(string $uri, array $data = [], array $headers = [])
    {
        return $this->putJson($uri, $data, $headers);
    }

    protected function jsonPatch(string $uri, array $data = [], array $headers = [])
    {
        return $this->patchJson($uri, $data, $headers);
    }

    protected function jsonDelete(string $uri, array $data = [], array $headers = [])
    {
        return $this->deleteJson($uri, $data, $headers);
    }

    protected function assertPaginatedStructure($response): void
    {
        $response->assertJsonStructure([
            'data',
            'links' => ['first','last','prev','next'],
            'meta'  => ['current_page','per_page','total','last_page'],
        ]);
    }

    protected function assertValidationError(string $field, $response): void
    {
        $response->assertStatus(422)->assertJsonValidationErrors([$field]);
    }

    protected function table(string|object $modelOrTable): string
    {
        if (is_string($modelOrTable) && is_subclass_of($modelOrTable, \Illuminate\Database\Eloquent\Model::class)) {
            return (new $modelOrTable)->getTable();
        }
        if (is_object($modelOrTable) && $modelOrTable instanceof \Illuminate\Database\Eloquent\Model) {
            return $modelOrTable->getTable();
        }
        return (string) $modelOrTable;
    }
}

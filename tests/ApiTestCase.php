<?php

namespace Tests;

use App\Models\User;
use Laravel\Sanctum\Sanctum;

abstract class ApiTestCase extends TestCase
{
    protected bool $authenticate = true;
    protected string $sanctumGuard = 'web';

    protected ?User $authUser = null;

    protected bool $seedPermissions = false;

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->authenticate) {
            $this->actingAsUser();
        }
    }

    protected function actingAsUser(?User $user = null, array $abilities = ['*']): User
    {
        $this->authUser = $user ?: User::factory()->create();
        Sanctum::actingAs($this->authUser, $abilities, $this->sanctumGuard);
        return $this->authUser;
    }

    protected function withoutAuth(): void
    {
        $this->authenticate = false;
        $this->authUser = null;
    }

    protected function asRole(string $role, ?User $user = null): User
    {
        $user = $user ?: ($this->authUser ?: $this->actingAsUser());
        if (class_exists(\Spatie\Permission\Models\Role::class)) {
            \Spatie\Permission\Models\Role::findOrCreate($role, 'web');
            $user->assignRole($role);
        }
        return $user;
    }

    protected function withPermissions(array $names, ?User $user = null): User
    {
        $user = $user ?: ($this->authUser ?: $this->actingAsUser());
        if (class_exists(\Spatie\Permission\Models\Permission::class)) {
            foreach ($names as $p) {
                \Spatie\Permission\Models\Permission::findOrCreate($p, 'web');
            }
            $user->givePermissionTo($names);
        }
        return $user;
    }
}

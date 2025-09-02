<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $entities = [
            'accounts','categories','products','companies','customers',
            'transaction-entries','transaction-items','stock-levels','stock-movements',
            'debts','account-users','messages','conversations'
        ];

        $actions = ['read','create','update','delete','import','export','bulk','restore'];

        foreach ($entities as $e) {
            foreach ($actions as $a) {
                Permission::findOrCreate("$e.$a", 'web');
            }
        }

        $admin = Role::findOrCreate('admin', 'web');
        $manager = Role::findOrCreate('manager', 'web');
        $user = Role::findOrCreate('user', 'web');

        // admin a tout
        $admin->givePermissionTo(Permission::all());

        // manager
        $managerPermissions = array_merge(
            array_map(fn($e) => "$e.read", $entities),
            array_map(fn($e) => "$e.create", $entities),
            array_map(fn($e) => "$e.update", $entities),
            array_map(fn($e) => "$e.export", $entities),
            array_map(fn($e) => "$e.bulk", $entities),
            array_map(fn($e) => "$e.restore", $entities)
        );
        $manager->syncPermissions(Permission::whereIn('name', $managerPermissions)->get());

        // user
        $userPermissions = array_merge(
            array_map(fn($e) => "$e.read", $entities),
            array_map(fn($e) => "$e.create", $entities),
            array_map(fn($e) => "$e.update", $entities)
        );
        $user->syncPermissions(Permission::whereIn('name', $userPermissions)->get());
    }
}

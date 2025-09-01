<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $rolesWithPermissions = $this->generateRolesWithPermissions();
        $directPermissions = '[]';

        static::makeRolesWithPermissions($rolesWithPermissions);
        static::makeDirectPermissions($directPermissions);
    }

    /**
     * Generate the roles with their permissions as a JSON string.
     *
     * @return string
     */
    protected function generateRolesWithPermissions(): string
    {
        $roles = [
            $this->getSuperAdminRole(),
            $this->getClientRole(),
            $this->getSupportRole(),
        ];

        return json_encode($roles);
    }

    /**
     * Define permissions for the super_admin role.
     *
     * @return array
     */
    protected function getSuperAdminRole(): array
    {
        return [
            'name' => 'super_admin',
            'guard_name' => 'web',
            'permissions' => Permission::all()->pluck('name')->toArray(),
        ];
    }

    /**
     * Define permissions for the client role.
     *
     * @return array
     */
    protected function getClientRole(): array
    {
        return [
            'name' => 'client',
            'guard_name' => 'web',
            'permissions' => [
                'view_tickets::ticket',
                'view_any_tickets::ticket',
                'create_tickets::ticket',
                'update_tickets::ticket',
            ],
        ];
    }

    /**
     * Define permissions for the support role.
     *
     * @return array
     */
    protected function getSupportRole(): array
    {
        return [
            'name' => 'support',
            'guard_name' => 'web',
            'permissions' => [
                'view_tickets::ticket',
                'view_any_tickets::ticket',
                'create_tickets::ticket',
                'update_tickets::ticket',
            ],
        ];
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (! blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            /** @var Model $roleModel */
            $roleModel = Utils::getRoleModel();
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($rolePlusPermissions as $rolePlusPermission) {
                $role = $roleModel::firstOrCreate([
                    'name' => $rolePlusPermission['name'],
                    'guard_name' => $rolePlusPermission['guard_name'],
                ]);

                if (! blank($rolePlusPermission['permissions'])) {
                    $permissionModels = collect($rolePlusPermission['permissions'])
                        ->map(fn ($permission) => $permissionModel::firstOrCreate([
                            'name' => $permission,
                            'guard_name' => $rolePlusPermission['guard_name'],
                        ]))
                        ->all();

                    $role->syncPermissions($permissionModels);
                }
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (! blank($permissions = json_decode($directPermissions, true))) {
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($permissions as $permission) {
                if ($permissionModel::whereName($permission)->doesntExist()) {
                    $permissionModel::create([
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'],
                    ]);
                }
            }
        }
    }
}

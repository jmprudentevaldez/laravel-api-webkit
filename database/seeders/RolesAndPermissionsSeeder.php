<?php

namespace Database\Seeders;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Standard user permissions
        Permission::create(['name' => PermissionEnum::VIEW_PROFILE, 'guard_name' => 'sanctum']);
        Permission::create(['name' => PermissionEnum::UPDATE_PROFILE, 'guard_name' => 'sanctum']);
        /** @var Role $standardRole */
        $standardRole = Role::create(['name' => RoleEnum::STANDARD_USER, 'guard_name' => 'sanctum']);
        $standardRole->givePermissionTo(Permission::all());

        // Admin Permissions
        Permission::create(['name' => PermissionEnum::CREATE_USERS, 'guard_name' => 'sanctum']);
        Permission::create(['name' => PermissionEnum::UPDATE_USERS, 'guard_name' => 'sanctum']);
        Permission::create(['name' => PermissionEnum::DELETE_USERS, 'guard_name' => 'sanctum']);
        Permission::create(['name' => PermissionEnum::VIEW_USERS, 'guard_name' => 'sanctum']);
        Permission::create(['name' => PermissionEnum::VIEW_USER_ROLES, 'guard_name' => 'sanctum']);
        Permission::create(['name' => PermissionEnum::UPDATE_APP_SETTINGS, 'guard_name' => 'sanctum']);
        /** @var Role $adminRole */
        $adminRole = Role::create(['name' => RoleEnum::ADMIN, 'guard_name' => 'sanctum']);
        $adminRole->givePermissionTo(Permission::all());

        // System Support Permissions
        $notification_per = Permission::create(['name' => PermissionEnum::RECEIVE_SYSTEM_ALERTS, 'guard_name' => 'sanctum']);
        /** @var Role $systemSupport */
        $systemSupport = Role::create(['name' => RoleEnum::SYSTEM_SUPPORT, 'guard_name' => 'sanctum']);
        $systemSupport->givePermissionTo($notification_per);

        /**
         * Superuser role. We allow all permissions through here
         *
         * @see \App\Providers\AuthServiceProvider
         *
         * @var Role $superUserRole
         */
        Role::create(['name' => RoleEnum::SUPER_USER, 'guard_name' => 'sanctum']);
    }
}

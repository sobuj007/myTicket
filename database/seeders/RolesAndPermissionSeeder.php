<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void

    {


        // app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPremission();
        // //1. create all permission 
        // $permissions = [
        //     'create',
        //     'edit',
        //     'view',
        //     'delete',
        //     'assign roles',
        //     'assign permission',
        // ];
        // // foreach ($permissions as $permission) {
        // //     Permission::firstOrCreate(['name' => $permission]);
        // // }
        // foreach ($permissions as $permission) {
        //     //  Permission::firstOrCreate(['name' => $permission]);
        //     Permission::firstOrCreate(['name' => $permission]);
        // }

        //  user create edit only
        $userRole = Role::firstOrCreate(['name' => 'user']);


        $supportRole = Role::firstOrCreate(['name' => 'support']);

        //  admin create ............
        $adminRole = Role::FirstOrcreate(['name' => 'admin']);
        // $adminRole->syncPermission(Permission::all());

        //
    }
}

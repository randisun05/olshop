<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Roles baku untuk platform toko online. Permission granular per modul
     * ditambahkan seiring modul tersebut dibangun (lihat docs/PERENCANAAN.md).
     */
    public function run(): void
    {
        $roles = [
            'Super Admin',
            'Admin',
            'Staff Gudang',
            'Staff CS',
            'Customer',
        ];

        foreach ($roles as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}

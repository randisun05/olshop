<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Permission granular ditambah per modul seiring modul tersebut dibangun.
     * Lihat docs/PERENCANAAN.md untuk daftar modul yang direncanakan.
     */
    public function run(): void
    {
        // Wajib: bersihkan cache permission spatie sebelum seeding, jika tidak
        // permission yang baru dibuat tidak akan terdeteksi sampai cache expired.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'categories.manage',
            'brands.manage',
            'attributes.manage',
            'products.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $superAdmin = Role::findByName('Super Admin', 'web');
        $superAdmin->syncPermissions($permissions);

        $admin = Role::findByName('Admin', 'web');
        $admin->syncPermissions($permissions);

        $staffGudang = Role::findByName('Staff Gudang', 'web');
        $staffGudang->syncPermissions(['products.manage']);
    }
}

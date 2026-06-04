<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Clear existing tables
        DB::table('role_permissions')->truncate();
        DB::table('permissions')->truncate();

        // Menus and actions
        $menus = [
            'dashboard',
            'users',
            'petugas',
            'pelanggan',
            'kriteria',
            'roles',
            'penagihan',
            'invoice',
            'penilaian',
            'ranking',
            'monitoring'
        ];

        $actions = ['view', 'create', 'edit', 'delete', 'export'];

        // Insert permissions
        $insertedPermissions = [];
        foreach ($menus as $menu) {
            foreach ($actions as $action) {
                $permissionName = "{$menu}:{$action}";
                $id = DB::table('permissions')->insertGetId([
                    'name' => $permissionName
                ]);
                $insertedPermissions[$permissionName] = $id;
            }
        }

        // Get Role IDs
        $adminId = DB::table('roles')->where('name', 'Admin')->value('id') ?? 1;
        $staffId = DB::table('roles')->where('name', 'Staff')->value('id') ?? 2;
        $supervisorId = DB::table('roles')->where('name', 'Supervisor')->value('id') ?? 3;

        // 1. ADMIN gets all permissions
        foreach ($insertedPermissions as $name => $id) {
            DB::table('role_permissions')->insert([
                'role_id' => $adminId,
                'permission_id' => $id
            ]);
        }

        // 2. STAFF permissions
        $staffPermNames = [
            'dashboard:view',
            'pelanggan:view', 'pelanggan:create', 'pelanggan:edit', 'pelanggan:export',
            'penagihan:view', 'penagihan:create', 'penagihan:edit', 'penagihan:export',
            'invoice:view', 'invoice:export',
            'ranking:view',
            'monitoring:view'
        ];

        foreach ($staffPermNames as $name) {
            if (isset($insertedPermissions[$name])) {
                DB::table('role_permissions')->insert([
                    'role_id' => $staffId,
                    'permission_id' => $insertedPermissions[$name]
                ]);
            }
        }

        // 3. SUPERVISOR permissions
        $supervisorPermNames = [
            'dashboard:view',
            'petugas:view',
            'pelanggan:view',
            'penilaian:view', 'penilaian:export',
            'ranking:view', 'ranking:export',
            'monitoring:view', 'monitoring:export'
        ];

        foreach ($supervisorPermNames as $name) {
            if (isset($insertedPermissions[$name])) {
                DB::table('role_permissions')->insert([
                    'role_id' => $supervisorId,
                    'permission_id' => $insertedPermissions[$name]
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert permissions back to the original set
        DB::table('role_permissions')->truncate();
        DB::table('permissions')->truncate();

        $originalPermissions = [
            'dashboard',
            'roles',
            'data_pengguna',
            'data_petugas',
            'data_kriteria',
            'data_pelanggan',
            'data_penilaian',
            'input_penagihan',
            'cetak_invoice',
            'hasil_ranking',
            'monitoring'
        ];

        foreach ($originalPermissions as $name) {
            DB::table('permissions')->insert([
                'name' => $name
            ]);
        }
    }
};

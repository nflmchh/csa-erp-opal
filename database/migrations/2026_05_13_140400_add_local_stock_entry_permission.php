<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        // Create permission
        $perm = Permission::firstOrCreate(['name' => 'create local stock entry']);

        // Assign to roles
        $roles = ['superadmin', 'owner', 'kepala toko', 'admin gudang'];
        foreach ($roles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($perm);
            }
        }
    }

    public function down(): void
    {
        $perm = Permission::where('name', 'create local stock entry')->first();
        if ($perm) {
            $perm->delete();
        }
    }
};

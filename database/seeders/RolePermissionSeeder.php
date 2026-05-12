<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // dashboard
            'view dashboard',

            // Auth & User Management
            'manage users',
            'manage roles',
            'manage permissions',
            'manage settings',

            // Master Data
            'view master',
            'create master',
            'update master',
            'delete master',

            // Product
            'view product',
            'create product',
            'update product',
            'delete product',
            'manage product',
            'print product label',

            // Warehouse
            'view warehouse',
            'create warehouse stock',
            'update warehouse stock',
            'adjust warehouse stock',
            'view warehouse dashboard',
            'manage warehouse',

            // Shipping
            'view shipment',
            'create shipment',
            'update shipment',
            'approve shipment',
            'receive shipment',
            'print shipment',
            'cancel shipment',

            // Store
            'view store',
            'view catalog',
            'manage store stock',
            'receive store shipment',
            'request store transfer',
            'approve store transfer',

            // Transfer
            'view transfer',
            'create transfer',
            'approve transfer',
            'receive transfer',
            'cancel transfer',
            'print transfer',

            // POS
            'access pos',
            'view pos',
            'process sale',
            'apply discount',
            'open cash session',
            'close cash session',
            'view cash session',

            // Return
            'view customer return',
            'process customer return',
            'view store return',
            'create store return',
            'approve store return',
            'receive store return',
            'inspect return',

            // Stock Opname
            'view stock opname',
            'create stock opname',
            'submit stock opname',
            'approve stock opname',
            'delete stock opname',

            // Finance
            'view finance',
            'export finance',
            'manage finance',

            // Reports
            'view report',
            'export report',
            'print report',

            // Audit Log
            'view audit log',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // SUPERADMIN — semua akses (bypass via Gate::before)
        $superadmin = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        $superadmin->syncPermissions(Permission::all());

        // OWNER
        $owner = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $owner->syncPermissions([
            'view dashboard',
            'view master',
            'view product',
            'view warehouse',
            'view warehouse dashboard',
            'view shipment',
            'print shipment',
            'view store',
            'view transfer',
            'print transfer',
            'view customer return',
            'view store return',
            'view stock opname',
            'view finance',
            'export finance',
            'view report',
            'export report',
            'print report',
            'view audit log',
        ]);

        // FINANCE
        $finance = Role::firstOrCreate(['name' => 'finance', 'guard_name' => 'web']);
        $finance->syncPermissions([
            'view master',
            'view product',
            'view warehouse',
            'view shipment',
            'view store',
            'view transfer',
            'view customer return',
            'view store return',
            'view stock opname',
            'view finance',
            'view pos',
            'view cash session',
            'export finance',
            'manage finance',
            'view report',
            'export report',
            'print report',
        ]);

        // ADMIN GUDANG
        $adminWarehouse = Role::firstOrCreate(['name' => 'admin gudang', 'guard_name' => 'web']);
        $adminWarehouse->syncPermissions([
            'view product',
            'view warehouse',
            'create warehouse stock',
            'update warehouse stock',
            'adjust warehouse stock',
            'view warehouse dashboard',
            'manage warehouse',
            'view shipment',
            'create shipment',
            'update shipment',
            'approve shipment',
            'receive shipment',
            'print shipment',
            'cancel shipment',
            'view store return',
            'approve store return',
            'receive store return',
            'inspect return',
        ]);

        // OPERATOR GUDANG
        $operatorWarehouse = Role::firstOrCreate(['name' => 'operator gudang', 'guard_name' => 'web']);
        $operatorWarehouse->syncPermissions([
            'view product',
            'view warehouse',
            'create warehouse stock',
            'update warehouse stock',
            'view warehouse dashboard',
            'view shipment',
            'update shipment',
            'receive shipment',
            'print shipment',
            'view store return',
            'receive store return',
            'view stock opname',
            'submit stock opname',
        ]);

        // KEPALA TOKO
        $storeHead = Role::firstOrCreate(['name' => 'kepala toko', 'guard_name' => 'web']);
        $storeHead->syncPermissions([
            'view product',
            'view store',
            'view catalog',
            'view pos',
            'view cash session',
            'manage store stock',
            'receive store shipment',
            'request store transfer',
            'approve store transfer',
            'view transfer',
            'receive transfer',
            'print transfer',
            'view customer return',
            'process customer return',
            'view store return',
            'create store return',
            'view stock opname',
            'create stock opname',
            'submit stock opname',
            'view report',
            'print report',
            'print product label'
        ]);

        // KASIR
        $cashier = Role::firstOrCreate(['name' => 'kasir', 'guard_name' => 'web']);
        $cashier->syncPermissions([
            'access pos',
            'view pos',
            'process sale',
            'apply discount',
            'open cash session',
            'close cash session',
            'view cash session',
        ]);
    }
}
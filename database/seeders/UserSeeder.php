<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $warehouse = Warehouse::first();
        $store     = Store::first();

        // Superadmin
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@sevenkey.id'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $superadmin->assignRole('superadmin');

        // Owner
        $owner = User::firstOrCreate(
            ['email' => 'owner@sevenkey.id'],
            [
                'name'     => 'Owner SevenKey',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $owner->assignRole('owner');

        // Finance
        $finance = User::firstOrCreate(
            ['email' => 'finance@sevenkey.id'],
            [
                'name'     => 'Finance Team',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $finance->assignRole('finance');

        // Admin Gudang
        $adminGudang = User::firstOrCreate(
            ['email' => 'admin.gudang@sevenkey.id'],
            [
                'name'     => 'Admin Gudang',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $adminGudang->assignRole('admin gudang');

        // Operator Gudang
        $operatorGudang = User::firstOrCreate(
            ['email' => 'operator.gudang@sevenkey.id'],
            [
                'name'     => 'Operator Gudang',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $operatorGudang->assignRole('operator gudang');

        // Kepala Toko
        $kepalaToko = User::firstOrCreate(
            ['email' => 'kepala.toko@sevenkey.id'],
            [
                'name'     => 'Kepala Toko 1',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $kepalaToko->assignRole('kepala toko');
        if ($store) {
            $kepalaToko->stores()->syncWithoutDetaching([$store->id => ['is_primary' => true]]);
        }

        // Kasir
        $kasir = User::firstOrCreate(
            ['email' => 'kasir@sevenkey.id'],
            [
                'name'     => 'Kasir Toko 1',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $kasir->assignRole('kasir');
        if ($store) {
            $kasir->stores()->syncWithoutDetaching([$store->id => ['is_primary' => true]]);
        }
    }
}

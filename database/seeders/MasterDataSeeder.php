<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\PaymentMethod;
use App\Models\ProductType;
use App\Models\ReturnReason;
use App\Models\Size;
use App\Models\Store;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Brands (5 brand fashion)
        $brands = [
            ['name' => 'Sevenkey Original', 'code' => 'SKO', 'slug' => 'sevenkey-original'],
            ['name' => 'Urban Wear',         'code' => 'UBW', 'slug' => 'urban-wear'],
            ['name' => 'Street Core',        'code' => 'STC', 'slug' => 'street-core'],
            ['name' => 'Classic Line',       'code' => 'CLN', 'slug' => 'classic-line'],
            ['name' => 'Active Fit',         'code' => 'ACF', 'slug' => 'active-fit'],
        ];
        foreach ($brands as $brand) {
            Brand::firstOrCreate(['code' => $brand['code']], array_merge($brand, ['is_active' => true]));
        }

        // Categories
        $categories = [
            ['name' => 'Atasan',  'code' => 'ATS', 'slug' => 'atasan'],
            ['name' => 'Bawahan', 'code' => 'BWH', 'slug' => 'bawahan'],
            ['name' => 'Outer',   'code' => 'OUT', 'slug' => 'outer'],
            ['name' => 'Aksesoris', 'code' => 'AKS', 'slug' => 'aksesoris'],
        ];
        foreach ($categories as $cat) {
            Category::firstOrCreate(['code' => $cat['code']], array_merge($cat, ['is_active' => true]));
        }

        // Product Types
        $atasan  = Category::where('code', 'ATS')->first();
        $bawahan = Category::where('code', 'BWH')->first();
        $outer   = Category::where('code', 'OUT')->first();

        $productTypes = [
            ['name' => 'Kaos',       'code' => 'KOS', 'slug' => 'kaos',        'category_id' => $atasan?->id],
            ['name' => 'Kemeja',     'code' => 'KMJ', 'slug' => 'kemeja',      'category_id' => $atasan?->id],
            ['name' => 'Polo Shirt', 'code' => 'PLO', 'slug' => 'polo-shirt',  'category_id' => $atasan?->id],
            ['name' => 'Hoodie',     'code' => 'HDI', 'slug' => 'hoodie',      'category_id' => $atasan?->id],
            ['name' => 'Celana Panjang', 'code' => 'CPJ', 'slug' => 'celana-panjang', 'category_id' => $bawahan?->id],
            ['name' => 'Celana Pendek', 'code' => 'CPD', 'slug' => 'celana-pendek', 'category_id' => $bawahan?->id],
            ['name' => 'Jaket',      'code' => 'JKT', 'slug' => 'jaket',       'category_id' => $outer?->id],
            ['name' => 'Sweater',    'code' => 'SWT', 'slug' => 'sweater',     'category_id' => $outer?->id],
        ];
        foreach ($productTypes as $type) {
            ProductType::firstOrCreate(['code' => $type['code']], array_merge($type, ['is_active' => true]));
        }

        // Colors
        $colors = [
            ['name' => 'Hitam',  'code' => 'BLK', 'hex_code' => '#000000'],
            ['name' => 'Putih',  'code' => 'WHT', 'hex_code' => '#FFFFFF'],
            ['name' => 'Abu',    'code' => 'GRY', 'hex_code' => '#808080'],
            ['name' => 'Navy',   'code' => 'NVY', 'hex_code' => '#001F5B'],
            ['name' => 'Merah',  'code' => 'RED', 'hex_code' => '#FF0000'],
            ['name' => 'Biru',   'code' => 'BLU', 'hex_code' => '#0000FF'],
            ['name' => 'Hijau',  'code' => 'GRN', 'hex_code' => '#008000'],
            ['name' => 'Kuning', 'code' => 'YLW', 'hex_code' => '#FFFF00'],
            ['name' => 'Coklat', 'code' => 'BRN', 'hex_code' => '#8B4513'],
            ['name' => 'Krem',   'code' => 'CRM', 'hex_code' => '#FFF5E4'],
        ];
        foreach ($colors as $color) {
            Color::firstOrCreate(['code' => $color['code']], array_merge($color, ['is_active' => true]));
        }

        // Sizes
        $sizes = [
            ['name' => 'XS',  'code' => 'XS',  'sort_order' => 1],
            ['name' => 'S',   'code' => 'S',   'sort_order' => 2],
            ['name' => 'M',   'code' => 'M',   'sort_order' => 3],
            ['name' => 'L',   'code' => 'L',   'sort_order' => 4],
            ['name' => 'XL',  'code' => 'XL',  'sort_order' => 5],
            ['name' => 'XXL', 'code' => 'XXL', 'sort_order' => 6],
            ['name' => '3XL', 'code' => '3XL', 'sort_order' => 7],
            ['name' => '28',  'code' => '28',  'sort_order' => 8],
            ['name' => '29',  'code' => '29',  'sort_order' => 9],
            ['name' => '30',  'code' => '30',  'sort_order' => 10],
            ['name' => '31',  'code' => '31',  'sort_order' => 11],
            ['name' => '32',  'code' => '32',  'sort_order' => 12],
            ['name' => '33',  'code' => '33',  'sort_order' => 13],
            ['name' => '34',  'code' => '34',  'sort_order' => 14],
        ];
        foreach ($sizes as $size) {
            Size::firstOrCreate(['code' => $size['code']], array_merge($size, ['is_active' => true]));
        }

        // Warehouses
        $warehouses = [
            ['name' => 'Gudang Utama', 'code' => 'GDG-01', 'city' => 'Jakarta', 'pic_name' => 'Admin Gudang'],
        ];
        foreach ($warehouses as $wh) {
            Warehouse::firstOrCreate(['code' => $wh['code']], array_merge($wh, ['is_active' => true]));
        }

        // Stores
        $stores = [
            ['name' => 'Toko Sevenkey 1', 'code' => 'TK-01', 'city' => 'Jakarta',  'pic_name' => 'Kepala Toko 1'],
            ['name' => 'Toko Sevenkey 2', 'code' => 'TK-02', 'city' => 'Bandung',  'pic_name' => 'Kepala Toko 2'],
            ['name' => 'Toko Sevenkey 3', 'code' => 'TK-03', 'city' => 'Surabaya', 'pic_name' => 'Kepala Toko 3'],
        ];
        foreach ($stores as $store) {
            Store::firstOrCreate(['code' => $store['code']], array_merge($store, ['is_active' => true]));
        }

        // Payment Methods
        $paymentMethods = [
            ['name' => 'Tunai',        'code' => 'CASH',  'type' => 'cash',     'sort_order' => 1],
            ['name' => 'Transfer Bank','code' => 'TF',    'type' => 'transfer', 'sort_order' => 2],
            ['name' => 'QRIS',         'code' => 'QRIS',  'type' => 'qris',     'sort_order' => 3],
            ['name' => 'Debit Card',   'code' => 'DEBIT', 'type' => 'card',     'sort_order' => 4],
            ['name' => 'Credit Card',  'code' => 'CC',    'type' => 'card',     'sort_order' => 5],
        ];
        foreach ($paymentMethods as $pm) {
            PaymentMethod::firstOrCreate(['code' => $pm['code']], array_merge($pm, ['is_active' => true]));
        }

        // Return Reasons
        $returnReasons = [
            ['name' => 'Cacat Produksi',  'code' => 'CACAT',  'type' => 'both'],
            ['name' => 'Salah Kirim',     'code' => 'SALAH',  'type' => 'both'],
            ['name' => 'Tidak Sesuai',    'code' => 'TDSUI',  'type' => 'customer'],
            ['name' => 'Rusak Pengiriman','code' => 'RUSAK',  'type' => 'store'],
            ['name' => 'Tidak Laku',      'code' => 'TDLKU',  'type' => 'store'],
            ['name' => 'Kelebihan Stok',  'code' => 'KBHST',  'type' => 'store'],
        ];
        foreach ($returnReasons as $rr) {
            ReturnReason::firstOrCreate(['code' => $rr['code']], array_merge($rr, ['is_active' => true]));
        }
    }
}

<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;

class SkuGeneratorService
{
    /**
     * Generate SKU: [BRAND]-[TYPE]-[MODEL]-[COLOR]-[SIZE]
     * e.g. SKO-BLS-A001-BLK-M
     */
    public static function generate(
        Brand $brand,
        string $modelCode,
        Color $color,
        Size $size
    ): string {
        $brandCode = strtoupper($brand->code);
        $model     = strtoupper($modelCode);
        $colorCode = strtoupper(substr(preg_replace('/\s+/', '', $color->name), 0, 4));
        $sizeCode  = strtoupper($size->name);

        $base = "{$brandCode}-{$model}-{$colorCode}-{$sizeCode}";

        // Ensure uniqueness
        if (! ProductVariant::withTrashed()->where('sku', $base)->exists()) {
            return $base;
        }

        $counter = 2;
        do {
            $candidate = "{$base}-{$counter}";
            $counter++;
        } while (ProductVariant::withTrashed()->where('sku', $candidate)->exists());

        return $candidate;
    }

    /**
     * Generate a unique model_code for a product.
     * e.g. A001, A002, ... Z999
     */
    public static function generateModelCode(string $brandCode): string
    {
        $prefix = strtoupper(substr($brandCode, 0, 1));
        $last   = Product::withTrashed()
            ->where('model_code', 'like', "{$prefix}%")
            ->orderByDesc('model_code')
            ->value('model_code');

        if (!$last) {
            return "{$prefix}001";
        }

        $num = (int) substr($last, strlen($prefix));
        return $prefix . str_pad($num + 1, 3, '0', STR_PAD_LEFT);
    }
}

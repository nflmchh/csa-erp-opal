<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\StockLedger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Mutate stock at a location and write a ledger entry.
     * qty: positive = stock in, negative = stock out
     */
    public static function mutate(
        ProductVariant $variant,
        string         $locationType,
        int            $locationId,
        int            $qty,
        string         $type,
        ?string        $note = null,
        ?string        $refType = null,
        ?int           $refId = null
    ): Stock {
        return DB::transaction(function () use ($variant, $locationType, $locationId, $qty, $type, $note, $refType, $refId) {
            $stock = Stock::firstOrCreate(
                [
                    'product_variant_id' => $variant->id,
                    'location_type'      => $locationType,
                    'location_id'        => $locationId,
                ],
                ['qty' => 0]
            );

            $before = $stock->qty;
            $after  = $before + $qty;

            if ($after < 0) {
                throw new \RuntimeException("Stok tidak cukup untuk SKU {$variant->sku}.");
            }

            $stock->update(['qty' => $after]);

            StockLedger::create([
                'product_variant_id' => $variant->id,
                'location_type'      => $locationType,
                'location_id'        => $locationId,
                'type'               => $type,
                'qty'                => $qty,
                'qty_before'         => $before,
                'qty_after'          => $after,
                'reference_type'     => $refType,
                'reference_id'       => $refId,
                'note'               => $note,
                'created_by'         => Auth::id(),
            ]);

            return $stock;
        });
    }
}

<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\Sale;

class InvoiceNumberService
{
    /**
     * Generate the next purchase invoice number: PUR-00001, PUR-00002, ...
     */
    public static function nextPurchaseNumber(): string
    {
        $last = Purchase::query()
            ->orderByDesc('id')
            ->value('invoice_no');

        $next = 1;
        if ($last && preg_match('/PUR-(\d+)/', $last, $m)) {
            $next = ((int) $m[1]) + 1;
        }

        return 'PUR-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Generate the next sale invoice number: INV-00001, INV-00002, ...
     */
    public static function nextSaleNumber(): string
    {
        $last = Sale::query()
            ->orderByDesc('id')
            ->value('invoice_no');

        $next = 1;
        if ($last && preg_match('/INV-(\d+)/', $last, $m)) {
            $next = ((int) $m[1]) + 1;
        }

        return 'INV-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
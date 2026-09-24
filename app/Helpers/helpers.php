<?php

use App\Models\Expense;

if (! function_exists('money')) {
    /**
     * Format a number as currency with AFN suffix.
     */
    function money($value, string $currency = 'AFN'): string
    {
        return number_format((float) $value, 2) . ' ' . $currency;
    }
}

if (! function_exists('money_plain')) {
    /**
     * Format a number as currency WITHOUT the suffix.
     */
    function money_plain($value): string
    {
        return number_format((float) $value, 2);
    }
}

if (! function_exists('expense_categories')) {
    /**
     * Shortcut to the Expense::categories() map.
     */
    function expense_categories(): array
    {
        return Expense::categories();
    }
}
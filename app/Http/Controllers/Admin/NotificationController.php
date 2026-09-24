<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;

class NotificationController extends Controller
{
    public function index()
    {
        $lowStockProducts = Product::with(['category', 'brand'])
            ->whereColumn('quantity', '<=', 'minimum_stock')
            ->where('quantity', '>', 0)
            ->orderBy('quantity')
            ->get();

        $outOfStockProducts = Product::with(['category', 'brand'])
            ->where('quantity', '<=', 0)
            ->orderBy('name')
            ->get();

        return view('admin.notifications.index', compact('lowStockProducts', 'outOfStockProducts'));
    }
}
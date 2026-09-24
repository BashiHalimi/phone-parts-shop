<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $data = Cache::remember('admin.dashboard', now()->addSeconds(60), function () {
            return $this->buildDashboardData();
        });

        return view('admin.dashboard', $data);
    }

    /**
     * Compute all dashboard values as plain arrays (safe for caching).
     */
    private function buildDashboardData(): array
    {
        $today          = Carbon::today();
        $thisMonthStart = Carbon::now()->startOfMonth();

        $todaySales = (float) Sale::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum('total');

        $todaySalesCount = (int) Sale::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->count();

        $todayPurchases = (float) Purchase::whereDate('created_at', $today)
            ->whereIn('status', ['received', 'pending'])
            ->sum('total');

        $monthSales = (float) Sale::where('created_at', '>=', $thisMonthStart)
            ->where('status', 'completed')
            ->sum('total');

        $monthPurchases = (float) Purchase::where('created_at', '>=', $thisMonthStart)
            ->whereIn('status', ['received', 'pending'])
            ->sum('total');

        $monthExpenses = (float) Expense::whereDate('date', '>=', $thisMonthStart)
            ->sum('amount');

        $todayProfit = (float) $this->profitForDateRange($today, $today);

        $totalProducts   = (int) Product::count();
        $lowStockCount   = (int) Product::whereColumn('quantity', '<=', 'minimum_stock')
            ->where('quantity', '>', 0)
            ->count();
        $outOfStockCount = (int) Product::where('quantity', '<=', 0)->count();
        $totalCustomers  = (int) Customer::count();
        $totalSuppliers  = (int) Supplier::count();

        // Convert to plain arrays — safe to serialize
        $lowStockProducts = Product::with('category:id,name')
            ->whereColumn('quantity', '<=', 'minimum_stock')
            ->orderBy('quantity')
            ->limit(10)
            ->get()
            ->map(function ($p) {
                return [
                    'id'            => $p->id,
                    'name'          => $p->name,
                    'sku'           => $p->sku,
                    'quantity'      => (int) $p->quantity,
                    'minimum_stock' => (int) $p->minimum_stock,
                    'category_name' => $p->category?->name,
                ];
            })
            ->values()
            ->toArray();

        // Sales chart data — last 30 days
        $from = Carbon::today()->subDays(29)->startOfDay();
        $to   = Carbon::today()->endOfDay();

        $salesByDay = Sale::selectRaw('DATE(created_at) as day, SUM(total) as total')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->where('status', 'completed')
            ->groupBy('day')
            ->pluck('total', 'day')
            ->toArray();

        $purchByDay = Purchase::selectRaw('DATE(created_at) as day, SUM(total) as total')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->whereIn('status', ['received', 'pending'])
            ->groupBy('day')
            ->pluck('total', 'day')
            ->toArray();

        $salesChartData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date  = Carbon::today()->subDays($i);
            $key   = $date->format('Y-m-d');
            $salesChartData[] = [
                'date'  => $key,
                'label' => $date->format('M d'),
                'sales' => (float) ($salesByDay[$key] ?? 0),
                'purch' => (float) ($purchByDay[$key] ?? 0),
            ];
        }

        // Top selling products — this month
        $topProducts = SaleItem::query()
            ->select(
                'product_id',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(subtotal) as total_revenue')
            )
            ->whereHas('sale', function ($q) use ($thisMonthStart) {
                $q->where('created_at', '>=', $thisMonthStart)
                  ->where('status', 'completed');
            })
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->with('product:id,name,sku')
            ->get()
            ->map(function ($row) {
                return [
                    'product_name'  => $row->product?->name,
                    'product_sku'   => $row->product?->sku,
                    'total_qty'     => (int) $row->total_qty,
                    'total_revenue' => (float) $row->total_revenue,
                ];
            })
            ->values()
            ->toArray();

        return [
            'todaySales'        => $todaySales,
            'todaySalesCount'   => $todaySalesCount,
            'todayPurchases'    => $todayPurchases,
            'todayProfit'       => $todayProfit,
            'monthSales'        => $monthSales,
            'monthPurchases'    => $monthPurchases,
            'monthExpenses'     => $monthExpenses,
            'totalProducts'     => $totalProducts,
            'lowStockCount'     => $lowStockCount,
            'outOfStockCount'   => $outOfStockCount,
            'totalCustomers'    => $totalCustomers,
            'totalSuppliers'    => $totalSuppliers,
            'lowStockProducts'  => $lowStockProducts,
            'salesChartData'    => $salesChartData,
            'topProducts'       => $topProducts,
        ];
    }

    private function profitForDateRange(Carbon $from, Carbon $to): float
    {
        $items = SaleItem::whereHas('sale', function ($q) use ($from, $to) {
            $q->whereDate('created_at', '>=', $from)
              ->whereDate('created_at', '<=', $to)
              ->where('status', 'completed');
        })->with('product:id,purchase_price')->get();

        $revenue = 0;
        $cost    = 0;

        foreach ($items as $item) {
            $revenue += (float) $item->subtotal;
            $cost    += $item->quantity * (float) ($item->product?->purchase_price ?? 0);
        }

        return $revenue - $cost;
    }
}
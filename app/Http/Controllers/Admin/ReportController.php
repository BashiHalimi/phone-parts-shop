<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Reports home — shows tabs to pick a report.
     */
    public function index()
    {
        return view('admin.reports.index');
    }

    /**
     * SALES REPORT
     */
    public function sales(Request $request)
{
    [$from, $to] = $this->parseRange($request);
    $customerId = $request->input('customer_id');

    // Base query (used for both totals and paginated list)
    $base = Sale::query()
        ->whereDate('created_at', '>=', $from)
        ->whereDate('created_at', '<=', $to)
        ->where('status', 'completed')
        ->when($customerId, fn ($q) => $q->where('customer_id', $customerId));

    // Totals (run first — do NOT chain orderBy)
    $totals = (clone $base)->selectRaw('
        COUNT(*) as total_count,
        SUM(subtotal) as total_subtotal,
        SUM(discount) as total_discount,
        SUM(total) as total_total,
        SUM(paid) as total_paid,
        SUM(balance) as total_balance
    ')->first();

    // Daily breakdown
    $daily = (clone $base)
        ->select(
            DB::raw('DATE(created_at) as day'),
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(total) as total')
        )
        ->groupBy('day')
        ->orderBy('day')
        ->get();

    // Paginated list (with eager loading)
    $sales = (clone $base)
        ->with(['customer', 'user'])
        ->orderByDesc('id')
        ->paginate(20)
        ->withQueryString();

    $customers = Customer::orderBy('name')->get();

    return view('admin.reports.sales', compact(
        'sales', 'totals', 'daily', 'customers', 'customerId', 'from', 'to'
    ));
}

    /**
     * PURCHASES REPORT
     */
    public function purchases(Request $request)
{
    [$from, $to] = $this->parseRange($request);
    $supplierId = $request->input('supplier_id');

    $base = Purchase::query()
        ->whereDate('created_at', '>=', $from)
        ->whereDate('created_at', '<=', $to)
        ->whereIn('status', ['received', 'pending'])
        ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId));

    // Totals — no orderBy
    $totals = (clone $base)->selectRaw('
        COUNT(*) as total_count,
        SUM(subtotal) as total_subtotal,
        SUM(discount) as total_discount,
        SUM(total) as total_total,
        SUM(paid) as total_paid,
        SUM(balance) as total_balance
    ')->first();

    // Daily breakdown
    $daily = (clone $base)
        ->select(
            DB::raw('DATE(created_at) as day'),
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(total) as total')
        )
        ->groupBy('day')
        ->orderBy('day')
        ->get();

    // Paginated list
    $purchases = (clone $base)
        ->with(['supplier', 'user'])
        ->orderByDesc('id')
        ->paginate(20)
        ->withQueryString();

    $suppliers = Supplier::orderBy('name')->get();

    return view('admin.reports.purchases', compact(
        'purchases', 'totals', 'daily', 'suppliers', 'supplierId', 'from', 'to'
    ));
}

    /**
     * STOCK REPORT
     */
    public function stock(Request $request)
    {
        $filter = $request->input('filter'); // low | out | ok

        $products = Product::with(['category', 'brand'])
            ->when($filter === 'low', fn ($q) => $q->whereColumn('quantity', '<=', 'minimum_stock')->where('quantity', '>', 0))
            ->when($filter === 'out', fn ($q) => $q->where('quantity', '<=', 0))
            ->when($filter === 'ok',  fn ($q) => $q->whereColumn('quantity', '>', 'minimum_stock'))
            ->orderBy('quantity')
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        $totalProducts   = Product::count();
        $lowStockCount   = Product::whereColumn('quantity', '<=', 'minimum_stock')->where('quantity', '>', 0)->count();
        $outOfStockCount = Product::where('quantity', '<=', 0)->count();
        $stockCostValue  = Product::selectRaw('SUM(quantity * purchase_price) as v')->value('v') ?? 0;
        $stockSaleValue  = Product::selectRaw('SUM(quantity * selling_price) as v')->value('v') ?? 0;

        return view('admin.reports.stock', compact(
            'products',
            'filter',
            'totalProducts',
            'lowStockCount',
            'outOfStockCount',
            'stockCostValue',
            'stockSaleValue'
        ));
    }

    /**
     * PROFIT REPORT
     */
    public function profit(Request $request)
    {
        [$from, $to] = $this->parseRange($request);

        // Revenue (completed sales)
        $revenue = (float) Sale::whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->where('status', 'completed')
            ->sum('total');

        // COGS: use current purchase_price of each product
        $items = SaleItem::whereHas('sale', function ($q) use ($from, $to) {
            $q->whereDate('created_at', '>=', $from)
              ->whereDate('created_at', '<=', $to)
              ->where('status', 'completed');
        })->with('product')->get();

        $cogs = 0;
        foreach ($items as $item) {
            $cogs += $item->quantity * (float) ($item->product?->purchase_price ?? 0);
        }

        $grossProfit = $revenue - $cogs;

        // Expenses in period
        $expenses = (float) Expense::whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->sum('amount');

        // Group expenses by category
        $expensesByCategory = Expense::whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $netProfit = $grossProfit - $expenses;

        return view('admin.reports.profit', compact(
            'from',
            'to',
            'revenue',
            'cogs',
            'grossProfit',
            'expenses',
            'expensesByCategory',
            'netProfit'
        ));
    }

    /**
     * Return [Carbon $from, Carbon $to]. Default = this month.
     */
    private function parseRange(Request $request): array
    {
        $from = $request->input('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : Carbon::now()->startOfMonth();

        $to = $request->input('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : Carbon::now()->endOfDay();

        return [$from, $to];
    }
}
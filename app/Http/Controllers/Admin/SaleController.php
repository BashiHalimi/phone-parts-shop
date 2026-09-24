<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Services\InvoiceNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $search   = $request->input('search');
        $customer = $request->input('customer_id');
        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');

        $sales = Sale::query()
            ->with(['customer', 'user'])
            ->when($search, function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn ($w) => $w->where('name', 'like', "%{$search}%"));
            })
            ->when($customer, fn ($q) => $q->where('customer_id', $customer))
            ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $customers = Customer::orderBy('name')->get();

        return view('admin.sales.index', compact('sales', 'customers', 'search', 'customer', 'dateFrom', 'dateTo'));
    }

    public function create()
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();

        $products = Product::where('status', 'active')
            ->where('quantity', '>', 0)
            ->orderBy('name')
            ->get()
            ->map(function ($p) {
                return [
                    'id'            => $p->id,
                    'name'          => $p->name,
                    'sku'           => $p->sku,
                    'selling_price' => (float) $p->selling_price,
                    'quantity'      => (int) $p->quantity,
                ];
            })
            ->values();

        return view('admin.sales.create', compact('customers', 'products'));
    }
    /**
 * Show the POS interface.
 */
    public function pos()
{
    $customers = Customer::where('status', 'active')->orderBy('name')->get();

    $products = Product::query()
        ->select(['id', 'name', 'sku', 'selling_price', 'quantity', 'image', 'category_id'])
        ->where('status', 'active')
        ->where('quantity', '>', 0)
        ->with('category:id,name')
        ->orderBy('name')
        ->limit(12)
        ->get()
        ->map(function ($p) {
            return [
                'id'            => $p->id,
                'name'          => $p->name,
                'sku'           => $p->sku,
                'selling_price' => (float) $p->selling_price,
                'quantity'      => (int) $p->quantity,
                'image'         => $p->image ? asset('storage/' . $p->image) : null,
                'category'      => $p->category?->name,
            ];
        })
        ->values();

    return view('admin.sales.pos', compact('customers', 'products'));
}

    /**
     * AJAX endpoint — search products for the POS grid.
     */
    public function ajaxProducts(Request $request)
    {
        $search = $request->input('q');

        $products = Product::query()
            ->where('status', 'active')
            ->where('quantity', '>', 0)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit(48)
            ->get()
            ->map(function ($p) {
                return [
                    'id'            => $p->id,
                    'name'          => $p->name,
                    'sku'           => $p->sku,
                    'selling_price' => (float) $p->selling_price,
                    'quantity'      => (int) $p->quantity,
                    'image'         => $p->image ? asset('storage/' . $p->image) : null,
                    'category'      => $p->category?->name,
                ];
            });

        return response()->json($products);
    }



    public function store(StoreSaleRequest $request)
    {
        $data = $request->validated();

        try {
            $sale = DB::transaction(function () use ($data) {

                // --- 1. Validate stock & compute totals
                $subtotal = 0;
                foreach ($data['items'] as $item) {
                    $product = Product::where('id', $item['product_id'])->lockForUpdate()->first();

                    if (!$product) {
                        throw new \RuntimeException('Product not found.');
                    }

                    if ($product->quantity < $item['quantity']) {
                        throw new \RuntimeException(
                            'Not enough stock for ' . $product->name .
                            ' (available: ' . $product->quantity .
                            ', requested: ' . $item['quantity'] . ').'
                        );
                    }

                    $subtotal += $item['quantity'] * $item['price'];
                }

                $discount = $data['discount'] ?? 0;
                $total    = max(0, $subtotal - $discount);
                $paid     = min($data['paid'], $total);
                $balance  = $total - $paid;

                // --- 2. Create sale
                $sale = Sale::create([
                    'invoice_no'     => InvoiceNumberService::nextSaleNumber(),
                    'customer_id'    => $data['customer_id'] ?? null,
                    'user_id'        => Auth::id(),
                    'subtotal'       => $subtotal,
                    'discount'       => $discount,
                    'total'          => $total,
                    'paid'           => $paid,
                    'balance'        => $balance,
                    'payment_method' => $data['payment_method'],
                    'status'         => $data['status'],
                ]);

                // --- 3. Create items + decrease stock + record movements
                foreach ($data['items'] as $item) {
                    $lineSubtotal = $item['quantity'] * $item['price'];

                    $sale->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity'   => $item['quantity'],
                        'price'      => $item['price'],
                        'subtotal'   => $lineSubtotal,
                    ]);

                    Product::where('id', $item['product_id'])
                        ->decrement('quantity', $item['quantity']);

                    StockMovement::create([
                        'product_id'     => $item['product_id'],
                        'user_id'        => Auth::id(),
                        'type'           => 'sale',
                        'quantity'       => -$item['quantity'],   // NEGATIVE
                        'reference_type' => Sale::class,
                        'reference_id'   => $sale->id,
                        'notes'          => 'Sale ' . $sale->invoice_no,
                    ]);
                }

                // --- 4. Update customer balance if credit sale with balance > 0
                if ($balance > 0 && !empty($data['customer_id'])) {
                    Customer::where('id', $data['customer_id'])
                        ->increment('balance', $balance);
                }

                return $sale;
            });

            return redirect()
                ->route('admin.sales.show', $sale)
                ->with('success', 'Sale recorded successfully. Invoice: ' . $sale->invoice_no);

        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show(Sale $sale)
    {
        $sale->load(['customer', 'user', 'items.product']);
        return view('admin.sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        return redirect()
            ->route('admin.sales.show', $sale)
            ->with('error', 'Editing a completed sale is not allowed for stock consistency. Create a return instead.');
    }

    public function update(Request $request, Sale $sale)
    {
        return redirect()->route('admin.sales.show', $sale);
    }

    public function destroy(Sale $sale)
    {
        try {
            DB::transaction(function () use ($sale) {
                $sale->load('items');

                foreach ($sale->items as $item) {
                    // Reverse stock
                    Product::where('id', $item->product_id)
                        ->increment('quantity', $item->quantity);
                }

                // Reverse customer balance
                if ($sale->balance > 0 && $sale->customer_id) {
                    Customer::where('id', $sale->customer_id)
                        ->decrement('balance', $sale->balance);
                }

                // Remove stock movements
                StockMovement::where('reference_type', Sale::class)
                    ->where('reference_id', $sale->id)
                    ->delete();

                $sale->items()->delete();
                $sale->delete();
            });

            return redirect()
                ->route('admin.sales.index')
                ->with('success', 'Sale deleted and stock reversed.');

        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.sales.index')
                ->with('error', 'Could not delete sale: ' . $e->getMessage());
        }
    }
}
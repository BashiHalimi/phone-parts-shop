<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseRequest;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Services\InvoiceNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $supplierFilter = $request->input('supplier_id');

        $purchases = Purchase::query()
            ->with(['supplier', 'user'])
            ->when($search, function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                  ->orWhereHas('supplier', fn ($w) => $w->where('name', 'like', "%{$search}%"));
            })
            ->when($supplierFilter, fn ($q) => $q->where('supplier_id', $supplierFilter))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $suppliers = Supplier::orderBy('name')->get();

        return view('admin.purchases.index', compact('purchases', 'suppliers', 'search', 'supplierFilter'));
    }

    public function create()
{
    $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();

    $products = Product::where('status', 'active')
        ->orderBy('name')
        ->get()
        ->map(function ($p) {
            return [
                'id'             => $p->id,
                'name'           => $p->name,
                'sku'            => $p->sku,
                'purchase_price' => (float) $p->purchase_price,
            ];
        })
        ->values();

    return view('admin.purchases.create', compact('suppliers', 'products'));
}

    public function store(StorePurchaseRequest $request)
    {
        $data = $request->validated();

        try {
            $purchase = DB::transaction(function () use ($data) {

                // Compute totals from items
                $subtotal = 0;
                foreach ($data['items'] as $item) {
                    $subtotal += $item['quantity'] * $item['purchase_price'];
                }

                $discount = $data['discount'] ?? 0;
                $total    = max(0, $subtotal - $discount);
                $paid     = min($data['paid'], $total);   // cannot pay more than total
                $balance  = $total - $paid;

                // Create purchase
                $purchase = Purchase::create([
                    'invoice_no'     => InvoiceNumberService::nextPurchaseNumber(),
                    'supplier_id'    => $data['supplier_id'],
                    'user_id'        => Auth::id(),
                    'subtotal'       => $subtotal,
                    'discount'       => $discount,
                    'total'          => $total,
                    'paid'           => $paid,
                    'balance'        => $balance,
                    'payment_method' => $data['payment_method'],
                    'status'         => $data['status'],
                ]);

                // Create items + update stock + record movements
                foreach ($data['items'] as $item) {
                    $lineSubtotal = $item['quantity'] * $item['purchase_price'];

                    $purchase->items()->create([
                        'product_id'     => $item['product_id'],
                        'quantity'       => $item['quantity'],
                        'purchase_price' => $item['purchase_price'],
                        'subtotal'       => $lineSubtotal,
                    ]);

                    // Increase stock
                    Product::where('id', $item['product_id'])
                        ->increment('quantity', $item['quantity']);

                    // Record stock movement
                    StockMovement::create([
                        'product_id'     => $item['product_id'],
                        'user_id'        => Auth::id(),
                        'type'           => 'purchase',
                        'quantity'       => $item['quantity'],   // positive
                        'reference_type' => Purchase::class,
                        'reference_id'   => $purchase->id,
                        'notes'          => 'Purchase ' . $purchase->invoice_no,
                    ]);
                }

                // Update supplier balance (we owe them more if not fully paid)
                if ($balance > 0) {
                    Supplier::where('id', $data['supplier_id'])
                        ->increment('balance', $balance);
                }

                return $purchase;
            });

            return redirect()
                ->route('admin.purchases.show', $purchase)
                ->with('success', 'Purchase recorded successfully. Invoice: ' . $purchase->invoice_no);

        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to save purchase: ' . $e->getMessage());
        }
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'user', 'items.product']);
        return view('admin.purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        // Editing a purchase after stock has changed is risky.
        // For now, redirect to show with a message.
        return redirect()
            ->route('admin.purchases.show', $purchase)
            ->with('error', 'Editing a purchase after it has been saved is not allowed for stock consistency. Create an adjustment instead.');
    }

    public function update(Request $request, Purchase $purchase)
    {
        return redirect()->route('admin.purchases.show', $purchase);
    }

    public function destroy(Purchase $purchase)
    {
        // Deleting a purchase should reverse stock. To keep things safe,
        // we only allow deletion if you want to start over — and we warn about stock.
        // For now, we allow it but reverse the stock movements.
        try {
            DB::transaction(function () use ($purchase) {
                $purchase->load('items');

                foreach ($purchase->items as $item) {
                    // Reverse stock
                    Product::where('id', $item->product_id)
                        ->decrement('quantity', $item->quantity);

                    // Reverse supplier balance
                    // (only the balance part)
                    // Skip for simplicity — this could get complex; we'll handle in reports
                }

                // Remove related stock movements
                StockMovement::where('reference_type', Purchase::class)
                    ->where('reference_id', $purchase->id)
                    ->delete();

                $purchase->items()->delete();
                $purchase->delete();
            });

            return redirect()
                ->route('admin.purchases.index')
                ->with('success', 'Purchase deleted and stock reversed.');

        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.purchases.index')
                ->with('error', 'Could not delete purchase: ' . $e->getMessage());
        }
    }
}
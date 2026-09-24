<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Global list of all payments.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $method = $request->input('method');

        $payments = Payment::query()
            ->with(['user' , 'payable'])
            ->when($search, function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('payable', function ($w) use ($search) {
                      $w->where('invoice_no', 'like', "%{$search}%");
                  });
            })
            ->when($method, fn ($q) => $q->where('method', $method))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.payments.index', compact('payments', 'search', 'method'));
    }

    /**
     * Record a payment for a Sale.
     */
    public function storeForSale(StorePaymentRequest $request, Sale $sale)
    {
        $data = $request->validated();

        if ($data['amount'] > $sale->balance) {
            return back()->with('error', 'Payment exceeds remaining balance (' . number_format((float)$sale->balance, 2) . ' AFN).');
        }

        try {
            DB::transaction(function () use ($sale, $data) {
                Payment::create([
                    'payable_type' => Sale::class,
                    'payable_id'   => $sale->id,
                    'amount'       => $data['amount'],
                    'method'       => $data['method'],
                    'reference'    => $data['reference'] ?? null,
                    'notes'        => $data['notes'] ?? null,
                    'user_id'      => Auth::id(),
                ]);

                $newPaid    = (float) $sale->paid + (float) $data['amount'];
                $newBalance = max(0, (float) $sale->total - $newPaid);

                $sale->update([
                    'paid'    => $newPaid,
                    'balance' => $newBalance,
                ]);

                // Reduce customer balance if this sale is tied to a customer
                if ($sale->customer_id && $sale->balance > 0) {
                    Customer::where('id', $sale->customer_id)
                        ->decrement('balance', $data['amount']);
                }
            });

            return redirect()
                ->route('admin.sales.show', $sale)
                ->with('success', 'Payment of ' . number_format((float)$data['amount'], 2) . ' AFN recorded.');

        } catch (\Throwable $e) {
            return back()->with('error', 'Could not record payment: ' . $e->getMessage());
        }
    }

    /**
     * Record a payment for a Purchase.
     */
    public function storeForPurchase(StorePaymentRequest $request, Purchase $purchase)
    {
        $data = $request->validated();

        if ($data['amount'] > $purchase->balance) {
            return back()->with('error', 'Payment exceeds remaining balance (' . number_format((float)$purchase->balance, 2) . ' AFN).');
        }

        try {
            DB::transaction(function () use ($purchase, $data) {
                Payment::create([
                    'payable_type' => Purchase::class,
                    'payable_id'   => $purchase->id,
                    'amount'       => $data['amount'],
                    'method'       => $data['method'],
                    'reference'    => $data['reference'] ?? null,
                    'notes'        => $data['notes'] ?? null,
                    'user_id'      => Auth::id(),
                ]);

                $newPaid    = (float) $purchase->paid + (float) $data['amount'];
                $newBalance = max(0, (float) $purchase->total - $newPaid);

                $purchase->update([
                    'paid'    => $newPaid,
                    'balance' => $newBalance,
                ]);

                // Reduce supplier balance
                if ($purchase->supplier_id && $purchase->balance > 0) {
                    Supplier::where('id', $purchase->supplier_id)
                        ->decrement('balance', $data['amount']);
                }
            });

            return redirect()
                ->route('admin.purchases.show', $purchase)
                ->with('success', 'Payment of ' . number_format((float)$data['amount'], 2) . ' AFN recorded.');

        } catch (\Throwable $e) {
            return back()->with('error', 'Could not record payment: ' . $e->getMessage());
        }
    }

    /**
     * Delete a payment (reverses balances).
     */
    public function destroy(Payment $payment)
    {
        try {
            DB::transaction(function () use ($payment) {
                $amount    = (float) $payment->amount;
                $payable   = $payment->payable;

                if ($payable instanceof Sale) {
                    $newPaid    = max(0, (float) $payable->paid - $amount);
                    $newBalance = (float) $payable->total - $newPaid;

                    $payable->update([
                        'paid'    => $newPaid,
                        'balance' => $newBalance,
                    ]);

                    if ($payable->customer_id) {
                        Customer::where('id', $payable->customer_id)->increment('balance', $amount);
                    }
                } elseif ($payable instanceof Purchase) {
                    $newPaid    = max(0, (float) $payable->paid - $amount);
                    $newBalance = (float) $payable->total - $newPaid;

                    $payable->update([
                        'paid'    => $newPaid,
                        'balance' => $newBalance,
                    ]);

                    if ($payable->supplier_id) {
                        Supplier::where('id', $payable->supplier_id)->increment('balance', $amount);
                    }
                }

                $payment->delete();
            });

            return back()->with('success', 'Payment deleted and balances reversed.');

        } catch (\Throwable $e) {
            return back()->with('error', 'Could not delete payment: ' . $e->getMessage());
        }
    }
}
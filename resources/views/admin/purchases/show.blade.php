@extends('layouts.admin')

@section('title', 'Purchase ' . $purchase->invoice_no)
@section('page-title', 'Purchase Details')

@section('content')

    <div class="mx-auto max-w-4xl space-y-4">

        {{-- Header --}}
        <div class="rounded-lg bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Purchase {{ $purchase->invoice_no }}</h2>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ $purchase->created_at->format('Y-m-d H:i') }} · by {{ $purchase->user?->name ?? '—' }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <button onclick="window.print()"
                            class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Print
                    </button>
                    <a href="{{ route('admin.purchases.index') }}"
                       class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Back
                    </a>
                </div>
            </div>
        </div>

        {{-- Info --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-gray-500">Supplier</h3>
                <p class="font-medium text-gray-800">{{ $purchase->supplier?->name ?? '—' }}</p>
                @if($purchase->supplier?->company)
                    <p class="text-sm text-gray-600">{{ $purchase->supplier->company }}</p>
                @endif
                @if($purchase->supplier?->phone)
                    <p class="text-sm text-gray-600">📞 {{ $purchase->supplier->phone }}</p>
                @endif
            </div>

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-gray-500">Payment</h3>
                <dl class="space-y-1 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Method</dt><dd class="font-medium text-gray-800">{{ ucfirst($purchase->payment_method) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Status</dt><dd class="font-medium text-gray-800">{{ ucfirst($purchase->status) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Paid</dt><dd class="font-medium text-green-700">{{ number_format((float)$purchase->paid, 2) }} AFN</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Balance</dt><dd class="font-medium text-red-700">{{ number_format((float)$purchase->balance, 2) }} AFN</dd></div>
                </dl>
            </div>
        </div>

        {{-- Items --}}
        <div class="overflow-hidden rounded-lg bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Product</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Qty</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Price</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($purchase->items as $item)
                        <tr>
                            <td class="px-4 py-3">
                                <span class="font-medium text-gray-800">{{ $item->product?->name ?? '—' }}</span>
                                <p class="text-xs text-gray-500">{{ $item->product?->sku }}</p>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-700">{{ $item->quantity }}</td>
                            <td class="px-4 py-3 text-right text-gray-700">{{ number_format((float)$item->purchase_price, 2) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-800">{{ number_format((float)$item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 text-sm">
                    <tr>
                        <td colspan="3" class="px-4 py-2 text-right text-gray-500">Subtotal</td>
                        <td class="px-4 py-2 text-right font-medium text-gray-800">{{ number_format((float)$purchase->subtotal, 2) }} AFN</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="px-4 py-2 text-right text-gray-500">Discount</td>
                        <td class="px-4 py-2 text-right font-medium text-gray-800">{{ number_format((float)$purchase->discount, 2) }} AFN</td>
                    </tr>
                    <tr class="border-t border-gray-200">
                        <td colspan="3" class="px-4 py-3 text-right font-semibold text-gray-700">Total</td>
                        <td class="px-4 py-3 text-right text-lg font-bold text-gray-900">{{ number_format((float)$purchase->total, 2) }} AFN</td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>

        {{-- Payments --}}
    <div class="rounded-lg bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500">Payments Made</h3>
            @if((float)$purchase->balance > 0)
                <button type="button" onclick="document.getElementById('paymentForm').classList.toggle('hidden')"
                        class="rounded-md bg-green-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-700">
                    + Add Payment
                </button>
            @endif
        </div>

        @if((float)$purchase->balance > 0)
            <form id="paymentForm" method="POST" action="{{ route('admin.purchases.payments.store', $purchase) }}"
                  class="mb-4 hidden rounded-md border border-gray-200 bg-gray-50 p-4">
                @csrf
                <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Amount (AFN) *</label>
                        <input type="number" step="0.01" min="0.01" max="{{ $purchase->balance }}" name="amount" required
                               value="{{ $purchase->balance }}"
                               class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Method *</label>
                        <select name="method" required
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Reference</label>
                        <input type="text" name="reference" placeholder="Optional"
                               class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <div class="flex items-end">
                        <button type="submit"
                                class="w-full rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                            Record Payment
                        </button>
                    </div>
                    <div class="md:col-span-4">
                        <label class="mb-1 block text-xs font-medium text-gray-600">Notes</label>
                        <input type="text" name="notes" placeholder="Optional notes"
                               class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                </div>
            </form>
        @endif

        @if($purchase->payments()->exists())
            <div class="overflow-hidden rounded-md border border-gray-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left font-medium text-gray-600">Date</th>
                            <th class="px-3 py-2 text-right font-medium text-gray-600">Amount</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-600">Method</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-600">Reference</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-600">By</th>
                            <th class="px-3 py-2 text-right font-medium text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($purchase->payments()->orderByDesc('id')->get() as $payment)
                            <tr>
                                <td class="px-3 py-2 text-gray-500 whitespace-nowrap">{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                                <td class="px-3 py-2 text-right font-medium text-green-700">{{ number_format((float)$payment->amount, 2) }}</td>
                                <td class="px-3 py-2 text-gray-600">{{ ucfirst($payment->method) }}</td>
                                <td class="px-3 py-2 text-gray-500">{{ $payment->reference ?: '—' }}</td>
                                <td class="px-3 py-2 text-gray-500">{{ $payment->user?->name ?? '—' }}</td>
                                <td class="px-3 py-2 text-right">
                                    <form method="POST" action="{{ route('admin.payments.destroy', $payment) }}"
                                          class="inline"
                                          onsubmit="return confirm('Delete this payment? Balance will be restored.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-800">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-gray-500">No payments recorded yet.</p>
        @endif
    </div>
    <style>
        @media print {
            aside, header, footer, button, a { display: none !important; }
            body { background: #fff !important; }
        }
    </style>

@endsection
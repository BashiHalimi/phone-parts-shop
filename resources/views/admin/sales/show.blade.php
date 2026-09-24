@extends('layouts.admin')

@section('title', 'Invoice ' . $sale->invoice_no)
@section('page-title', 'Invoice')

@section('content')

    <div class="mx-auto max-w-4xl space-y-4">

        {{-- Top actions --}}
        <div class="flex justify-end gap-2 print:hidden">
            <button onclick="window.print()"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                Print Invoice
            </button>
            <a href="{{ route('admin.sales.index') }}"
               class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Back to Sales
            </a>
        </div>

        {{-- Invoice card --}}
        <div class="rounded-lg bg-white p-6 shadow-sm" id="invoiceCard">

            {{-- Header --}}
            <div class="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">PHONE PARTS SHOP</h1>
                    <p class="text-sm text-gray-500">Mobile Spare Parts Retail</p>
                </div>
                <div class="text-right">
                    <p class="text-lg font-semibold text-gray-800">INVOICE</p>
                    <p class="text-sm text-gray-500">{{ $sale->invoice_no }}</p>
                    <p class="text-sm text-gray-500">{{ $sale->created_at->format('Y-m-d H:i') }}</p>
                </div>
            </div>

            {{-- Meta --}}
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Customer</p>
                    @if($sale->customer)
                        <p class="mt-1 font-medium text-gray-800">{{ $sale->customer->name }}</p>
                        @if($sale->customer->phone)<p class="text-sm text-gray-600">{{ $sale->customer->phone }}</p>@endif
                        @if($sale->customer->address)<p class="text-sm text-gray-600">{{ $sale->customer->address }}</p>@endif
                    @else
                        <p class="mt-1 font-medium text-gray-800">Walk-in Customer</p>
                    @endif
                </div>
                <div class="sm:text-right">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Served By</p>
                    <p class="mt-1 font-medium text-gray-800">{{ $sale->user?->name ?? '—' }}</p>
                    <p class="text-sm text-gray-500">Payment: {{ ucfirst($sale->payment_method) }}</p>
                    <p class="text-sm text-gray-500">Status: {{ ucfirst($sale->status) }}</p>
                </div>
            </div>

            {{-- Items --}}
            <table class="mb-6 min-w-full text-sm">
                <thead class="border-b border-gray-200">
                    <tr class="text-left text-gray-600">
                        <th class="py-2 font-medium">Product</th>
                        <th class="py-2 text-right font-medium">Qty</th>
                        <th class="py-2 text-right font-medium">Price</th>
                        <th class="py-2 text-right font-medium">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $item)
                        <tr class="border-b border-gray-100">
                            <td class="py-2">
                                <span class="font-medium text-gray-800">{{ $item->product?->name ?? '—' }}</span>
                                <p class="text-xs text-gray-500">{{ $item->product?->sku }}</p>
                            </td>
                            <td class="py-2 text-right">{{ $item->quantity }}</td>
                            <td class="py-2 text-right">{{ number_format((float)$item->price, 2) }}</td>
                            <td class="py-2 text-right font-medium">{{ number_format((float)$item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Totals --}}
            <div class="ml-auto max-w-xs space-y-1 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Subtotal</span>
                    <span class="font-medium">{{ number_format((float)$sale->subtotal, 2) }} AFN</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Discount</span>
                    <span class="font-medium">{{ number_format((float)$sale->discount, 2) }} AFN</span>
                </div>
                <div class="flex justify-between border-t border-gray-200 pt-1">
                    <span class="font-semibold">Total</span>
                    <span class="text-base font-bold">{{ number_format((float)$sale->total, 2) }} AFN</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Paid</span>
                    <span class="font-medium text-green-700">{{ number_format((float)$sale->paid, 2) }} AFN</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Balance</span>
                    <span class="font-medium text-red-700">{{ number_format((float)$sale->balance, 2) }} AFN</span>
                </div>
            </div>

            {{-- Footer --}}
            <div class="mt-8 border-t border-gray-200 pt-4 text-center text-xs text-gray-500">
                Thank you for your business!
            </div>
        </div>

        {{-- ==================== PAYMENTS SECTION ==================== --}}
        <div class="rounded-lg bg-white p-6 shadow-sm print:hidden">

            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500">
                    Payments Received
                </h3>

                <button type="button"
        onclick="document.getElementById('paymentForm').classList.toggle('hidden')"
        style="background-color:#16a34a; color:#ffffff; padding:8px 16px; border-radius:6px; font-size:14px; font-weight:500; border:none; cursor:pointer;">
    + Add Payment
</button>
            </div>

            {{-- Balance info bar --}}
            <div class="mb-3 rounded-md bg-gray-50 p-3 text-xs text-gray-600">
                Total: <strong>{{ number_format((float)$sale->total, 2) }}</strong> AFN ·
                Paid: <strong>{{ number_format((float)$sale->paid, 2) }}</strong> AFN ·
                Balance:
                <strong class="{{ (float)$sale->balance > 0 ? 'text-red-700' : 'text-green-700' }}">
                    {{ number_format((float)$sale->balance, 2) }}
                </strong> AFN
            </div>

            {{-- Add payment form --}}
            <form id="paymentForm" method="POST"
                  action="{{ route('admin.sales.payments.store', $sale) }}"
                  class="mb-4 hidden rounded-md border border-gray-200 bg-gray-50 p-4">
                @csrf

                <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Amount (AFN) *</label>
                        <input type="number" step="0.01" min="0.01" max="{{ $sale->balance }}"
                               name="amount" required
                               value="{{ $sale->balance }}"
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
        style="background-color:#16a34a; color:#ffffff; padding:8px 16px; border-radius:6px; font-size:14px; font-weight:500; border:none; cursor:pointer; width:100%;">
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

            {{-- Payment history --}}
            @php
                $paymentRows = $sale->payments()->orderByDesc('id')->get();
            @endphp

            @if($paymentRows->isNotEmpty())
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
                            @foreach($paymentRows as $payment)
                                <tr>
                                    <td class="px-3 py-2 text-gray-500 whitespace-nowrap">
                                        {{ $payment->created_at->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="px-3 py-2 text-right font-medium text-green-700">
                                        {{ number_format((float)$payment->amount, 2) }}
                                    </td>
                                    <td class="px-3 py-2 text-gray-600">{{ ucfirst($payment->method) }}</td>
                                    <td class="px-3 py-2 text-gray-500">{{ $payment->reference ?: '—' }}</td>
                                    <td class="px-3 py-2 text-gray-500">{{ $payment->user?->name ?? '—' }}</td>
                                    <td class="px-3 py-2 text-right">
                                        <form method="POST"
                                              action="{{ route('admin.payments.destroy', $payment) }}"
                                              class="inline"
                                              onsubmit="return confirm('Delete this payment? Balance will be restored.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-xs font-medium text-red-600 hover:text-red-800">
                                                Delete
                                            </button>
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
        {{-- ==================== /PAYMENTS SECTION ==================== --}}

    </div>

    <style>
        @media print {
            body { background: #fff !important; }
            aside, header, footer, .print\:hidden { display: none !important; }
            main { padding: 0 !important; }
            #invoiceCard { box-shadow: none !important; }
        }
    </style>

@endsection
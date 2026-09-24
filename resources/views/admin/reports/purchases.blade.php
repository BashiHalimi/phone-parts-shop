@extends('layouts.admin')

@section('title', 'Purchase Report')
@section('page-title', 'Purchase Report')

@section('content')

    <div class="mb-4 rounded-lg bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.reports.purchases') }}"
              class="grid grid-cols-1 gap-3 md:grid-cols-4">
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">From</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}"
                       class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">To</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}"
                       class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">Supplier</label>
                <select name="supplier_id"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" @selected((string)$supplierId === (string)$s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit"
                        class="flex-1 rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    Apply
                </button>
                <a href="{{ route('admin.reports.purchases') }}"
                   class="flex-1 rounded-md border border-gray-300 px-4 py-2 text-center text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        <div class="rounded-lg bg-white p-4 shadow-sm">
            <p class="text-xs text-gray-500">Purchases</p>
            <p class="mt-1 text-lg font-bold text-gray-800">{{ (int)($totals->total_count ?? 0) }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-sm">
            <p class="text-xs text-gray-500">Subtotal</p>
            <p class="mt-1 text-lg font-bold text-gray-800">{{ number_format((float)($totals->total_subtotal ?? 0), 2) }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-sm">
            <p class="text-xs text-gray-500">Discount</p>
            <p class="mt-1 text-lg font-bold text-gray-800">{{ number_format((float)($totals->total_discount ?? 0), 2) }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-sm">
            <p class="text-xs text-gray-500">Total</p>
            <p class="mt-1 text-lg font-bold text-purple-700">{{ number_format((float)($totals->total_total ?? 0), 2) }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-sm">
            <p class="text-xs text-gray-500">Paid</p>
            <p class="mt-1 text-lg font-bold text-green-700">{{ number_format((float)($totals->total_paid ?? 0), 2) }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-sm">
            <p class="text-xs text-gray-500">Balance</p>
            <p class="mt-1 text-lg font-bold text-red-700">{{ number_format((float)($totals->total_balance ?? 0), 2) }}</p>
        </div>
    </div>

    <div class="mb-4 rounded-lg bg-white p-5 shadow-sm">
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-gray-500">Daily Breakdown</h3>
        @if($daily->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="border-b border-gray-200">
                        <tr class="text-left text-gray-500">
                            <th class="py-2 font-medium">Date</th>
                            <th class="py-2 text-right font-medium">Count</th>
                            <th class="py-2 text-right font-medium">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($daily as $d)
                            <tr>
                                <td class="py-2 text-gray-700">{{ $d->day }}</td>
                                <td class="py-2 text-right text-gray-700">{{ $d->count }}</td>
                                <td class="py-2 text-right font-medium text-gray-800">{{ number_format((float)$d->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-gray-500">No purchases in this period.</p>
        @endif
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        <div class="border-b border-gray-200 p-4">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500">Individual Purchases</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Invoice</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Date</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Supplier</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Total</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Paid</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($purchases as $purchase)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.purchases.show', $purchase) }}" class="font-medium text-indigo-600 hover:underline">
                                    {{ $purchase->invoice_no }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ $purchase->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $purchase->supplier?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-800">{{ number_format((float)$purchase->total, 2) }}</td>
                            <td class="px-4 py-3 text-right text-green-700">{{ number_format((float)$purchase->paid, 2) }}</td>
                            <td class="px-4 py-3 text-right text-red-700">{{ number_format((float)$purchase->balance, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">No purchases found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $purchases->links() }}</div>

@endsection
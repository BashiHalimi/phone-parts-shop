@extends('layouts.admin')

@section('title', 'Stock Report')
@section('page-title', 'Stock Report')

@section('content')

    <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Total Products</p>
            <p class="mt-2 text-2xl font-bold text-gray-800">{{ $totalProducts }}</p>
        </div>
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Low Stock</p>
            <p class="mt-2 text-2xl font-bold text-amber-600">{{ $lowStockCount }}</p>
        </div>
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Out of Stock</p>
            <p class="mt-2 text-2xl font-bold text-red-600">{{ $outOfStockCount }}</p>
        </div>
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Stock Value</p>
            <p class="mt-2 text-lg font-bold text-gray-800">{{ number_format((float)$stockCostValue, 2) }}</p>
            <p class="text-xs text-gray-500">cost · retail: {{ number_format((float)$stockSaleValue, 2) }}</p>
        </div>
    </div>

    <div class="mb-4 rounded-lg bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.reports.stock') }}" class="flex flex-wrap items-center gap-3">
            <label class="text-sm font-medium text-gray-700">Filter:</label>
            <select name="filter" onchange="this.form.submit()"
                    class="rounded-md border border-gray-300 px-3 py-2 text-sm">
                <option value="">All Products</option>
                <option value="ok"  @selected($filter === 'ok')>OK</option>
                <option value="low" @selected($filter === 'low')>Low Stock</option>
                <option value="out" @selected($filter === 'out')>Out of Stock</option>
            </select>
            <a href="{{ route('admin.reports.stock') }}"
               class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Reset
            </a>
        </form>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Product</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Category</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Qty</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Min</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Cost Value</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Retail Value</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($products as $p)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800">{{ $p->name }}</p>
                                <p class="text-xs text-gray-500">{{ $p->sku }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $p->category?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-800">{{ $p->quantity }}</td>
                            <td class="px-4 py-3 text-right text-gray-500">{{ $p->minimum_stock }}</td>
                            <td class="px-4 py-3 text-right text-gray-700">
                                {{ number_format($p->quantity * (float)$p->purchase_price, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right text-gray-700">
                                {{ number_format($p->quantity * (float)$p->selling_price, 2) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($p->quantity <= 0)
                                    <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">Out</span>
                                @elseif($p->quantity <= $p->minimum_stock)
                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">Low</span>
                                @else
                                    <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">OK</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $products->links() }}</div>

@endsection
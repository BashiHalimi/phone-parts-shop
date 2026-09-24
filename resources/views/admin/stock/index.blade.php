@extends('layouts.admin')

@section('title', 'Stock')
@section('page-title', 'Stock Overview')

@section('content')

    {{-- Summary cards --}}
    <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
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
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Stock Value (cost)</p>
            <p class="mt-2 text-2xl font-bold text-gray-800">{{ number_format((float)$stockValue, 2) }}</p>
            <p class="text-xs text-gray-500">AFN</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-4 rounded-lg bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.stock.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-4">
            <div class="md:col-span-2">
                <input type="text" name="search" value="{{ $search ?? '' }}"
                       placeholder="Search name, SKU, or model..."
                       class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
            <div>
                <select name="filter"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">All Products</option>
                    <option value="ok"  @selected($filter === 'ok')>OK (above minimum)</option>
                    <option value="low" @selected($filter === 'low')>Low Stock</option>
                    <option value="out" @selected($filter === 'out')>Out of Stock</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                        class="flex-1 rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    Apply
                </button>
                <a href="{{ route('admin.stock.index') }}"
                   class="flex-1 rounded-md border border-gray-300 px-4 py-2 text-center text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Product</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Category</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600">Current Stock</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600">Minimum</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600">Status</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800">{{ $product->name }}</p>
                                <p class="text-xs text-gray-500">{{ $product->sku }}@if($product->model) · {{ $product->model }}@endif</p>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $product->category?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-bold text-gray-800">{{ $product->quantity }}</td>
                            <td class="px-4 py-3 text-center text-gray-500">{{ $product->minimum_stock }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($product->isOutOfStock())
                                    <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">Out</span>
                                @elseif($product->isLowStock())
                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">Low</span>
                                @else
                                    <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">OK</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
    @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.stock.adjust.form', $product) }}"
           class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Adjust</a>
    @endif
    <a href="{{ route('admin.stock.movements', ['product_id' => $product->id]) }}"
       class="ml-3 text-sm font-medium text-gray-600 hover:text-gray-900">History</a>
</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $products->links() }}</div>

@endsection
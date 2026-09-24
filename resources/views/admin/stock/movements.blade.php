@extends('layouts.admin')

@section('title', 'Stock Movements')
@section('page-title', 'Stock Movements')

@section('content')

    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-500">Every change to stock is recorded here for audit.</p>
        <a href="{{ route('admin.stock.index') }}"
           class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            ← Back to Stock
        </a>
    </div>

    {{-- Filters --}}
    <div class="mb-4 rounded-lg bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.stock.movements') }}" class="grid grid-cols-1 gap-3 md:grid-cols-6">
            <div class="md:col-span-2">
                <input type="text" name="search" value="{{ $search ?? '' }}"
                       placeholder="Search product name or SKU..."
                       class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>

            <div>
                <select name="product_id"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">All Products</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" @selected((string)$productId === (string)$p->id)>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="type"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">All Types</option>
                    <option value="purchase"   @selected($type === 'purchase')>Purchase</option>
                    <option value="sale"       @selected($type === 'sale')>Sale</option>
                    <option value="return"     @selected($type === 'return')>Return</option>
                    <option value="adjustment" @selected($type === 'adjustment')>Adjustment</option>
                    <option value="damage"     @selected($type === 'damage')>Damage</option>
                </select>
            </div>

            <div>
                <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}"
                       class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>

            <div>
                <input type="date" name="date_to" value="{{ $dateTo ?? '' }}"
                       class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>

            <div class="md:col-span-6 flex gap-2">
                <button type="submit"
                        class="rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    Apply Filters
                </button>
                <a href="{{ route('admin.stock.movements') }}"
                   class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
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
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Date</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Product</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Type</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Change</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">By</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($movements as $m)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $m->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800">{{ $m->product?->name ?? '—' }}</p>
                                <p class="text-xs text-gray-500">{{ $m->product?->sku }}</p>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $colors = [
                                        'purchase'   => 'bg-green-100 text-green-700',
                                        'sale'       => 'bg-indigo-100 text-indigo-700',
                                        'return'     => 'bg-blue-100 text-blue-700',
                                        'adjustment' => 'bg-amber-100 text-amber-700',
                                        'damage'     => 'bg-red-100 text-red-700',
                                    ];
                                @endphp
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $colors[$m->type] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ ucfirst($m->type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-bold whitespace-nowrap {{ $m->quantity >= 0 ? 'text-green-700' : 'text-red-700' }}">
                                {{ $m->quantity > 0 ? '+' : '' }}{{ $m->quantity }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $m->user?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ \Illuminate\Support\Str::limit($m->notes, 50) ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">No stock movements recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $movements->links() }}</div>

@endsection
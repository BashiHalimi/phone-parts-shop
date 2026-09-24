@extends('layouts.admin')

@section('title', $product->name)
@section('page-title', 'Product Details')

@section('content')

    <div class="mx-auto max-w-4xl space-y-4">

        {{-- Header --}}
        <div class="rounded-lg bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row">
                <div class="md:w-40">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}"
                             alt="{{ $product->name }}"
                             class="h-40 w-40 rounded-lg object-cover">
                    @else
                        <div class="flex h-40 w-40 items-center justify-center rounded-lg bg-gray-100 text-sm text-gray-400">
                            No image
                        </div>
                    @endif
                </div>

                <div class="flex-1">
                    <h2 class="text-xl font-semibold text-gray-800">{{ $product->name }}</h2>
                    <p class="mt-1 text-sm text-gray-500">SKU: {{ $product->sku }}</p>
                    @if($product->model)
                        <p class="text-sm text-gray-500">Model: {{ $product->model }}</p>
                    @endif

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        @if($product->status === 'active')
                            <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">Active</span>
                        @else
                            <span class="rounded-full bg-gray-200 px-2 py-0.5 text-xs font-medium text-gray-700">Inactive</span>
                        @endif

                        @if($product->isOutOfStock())
                            <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">Out of Stock</span>
                        @elseif($product->isLowStock())
                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">Low Stock</span>
                        @endif
                    </div>

                    <div class="mt-4 flex gap-3">
    @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.products.edit', $product) }}"
           class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
            Edit Product
        </a>
        <a href="{{ route('admin.stock.adjust.form', $product) }}"
           class="rounded-md bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700">
            Adjust Stock
        </a>
    @endif
    <a href="{{ route('admin.products.index') }}"
       class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
        Back to List
    </a>
</div>
                    
                </div>
            </div>
        </div>

        {{-- Details --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-gray-500">Classification</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Category</dt>
                        <dd class="font-medium text-gray-800">{{ $product->category?->name ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Brand</dt>
                        <dd class="font-medium text-gray-800">{{ $product->brand?->name ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Supplier</dt>
                        <dd class="font-medium text-gray-800">{{ $product->supplier?->name ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-gray-500">Pricing & Stock</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Purchase Price</dt>
                        <dd class="font-medium text-gray-800">{{ number_format((float)$product->purchase_price, 2) }} AFN</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Selling Price</dt>
                        <dd class="font-medium text-gray-800">{{ number_format((float)$product->selling_price, 2) }} AFN</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Profit / Unit</dt>
                        <dd class="font-medium {{ $product->profitPerUnit() >= 0 ? 'text-green-700' : 'text-red-700' }}">
                            {{ number_format($product->profitPerUnit(), 2) }} AFN
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">In Stock</dt>
                        <dd class="font-medium text-gray-800">{{ $product->quantity }} units</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Minimum Stock</dt>
                        <dd class="font-medium text-gray-800">{{ $product->minimum_stock }} units</dd>
                    </div>
                </dl>
            </div>
        </div>

        @if($product->description)
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-gray-500">Description</h3>
                <p class="whitespace-pre-line text-sm text-gray-700">{{ $product->description }}</p>
            </div>
        @endif

    </div>

@endsection
@extends('layouts.admin')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('content')

    {{-- Summary --}}
    <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Total Alerts</p>
            <p class="mt-2 text-2xl font-bold text-gray-800">{{ $navTotalAlerts ?? 0 }}</p>
        </div>
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Low Stock</p>
            <p class="mt-2 text-2xl font-bold text-amber-600">{{ $navLowStockCount ?? 0 }}</p>
        </div>
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Out of Stock</p>
            <p class="mt-2 text-2xl font-bold text-red-600">{{ $navOutOfStockCount ?? 0 }}</p>
        </div>
    </div>

    {{-- Out of stock (red first — more urgent) --}}
    @if($outOfStockProducts->isNotEmpty())
        <div class="mb-4 overflow-hidden rounded-lg border border-red-200 bg-white shadow-sm">
            <div class="flex items-center gap-2 border-b border-red-100 bg-red-50 px-4 py-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 text-red-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <h3 class="text-sm font-semibold text-red-800">
                    Out of Stock ({{ $outOfStockProducts->count() }})
                </h3>
            </div>
            <ul class="divide-y divide-gray-100">
                @foreach($outOfStockProducts as $p)
                    <li class="flex items-center justify-between px-4 py-3">
                        <div class="min-w-0">
                            <p class="font-medium text-gray-800">{{ $p->name }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $p->sku }}
                                @if($p->category) · {{ $p->category->name }} @endif
                                @if($p->brand) · {{ $p->brand->name }} @endif
                            </p>
                        </div>
                        <div class="ml-3 flex items-center gap-2">
                            <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">
                                0 in stock
                            </span>
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.stock.adjust.form', $p) }}"
                                   class="rounded-md bg-gray-800 px-3 py-1.5 text-xs font-medium text-white hover:bg-gray-700">
                                    Restock
                                </a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Low stock --}}
    @if($lowStockProducts->isNotEmpty())
        <div class="mb-4 overflow-hidden rounded-lg border border-amber-200 bg-white shadow-sm">
            <div class="flex items-center gap-2 border-b border-amber-100 bg-amber-50 px-4 py-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 text-amber-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <h3 class="text-sm font-semibold text-amber-800">
                    Low Stock ({{ $lowStockProducts->count() }})
                </h3>
            </div>
            <ul class="divide-y divide-gray-100">
                @foreach($lowStockProducts as $p)
                    <li class="flex items-center justify-between px-4 py-3">
                        <div class="min-w-0">
                            <p class="font-medium text-gray-800">{{ $p->name }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $p->sku }}
                                @if($p->category) · {{ $p->category->name }} @endif
                                @if($p->brand) · {{ $p->brand->name }} @endif
                            </p>
                        </div>
                        <div class="ml-3 flex items-center gap-2">
                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">
                                {{ $p->quantity }} left (min {{ $p->minimum_stock }})
                            </span>
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.stock.adjust.form', $p) }}"
                                   class="rounded-md bg-gray-800 px-3 py-1.5 text-xs font-medium text-white hover:bg-gray-700">
                                    Restock
                                </a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- All clear --}}
    @if($lowStockProducts->isEmpty() && $outOfStockProducts->isEmpty())
        <div class="rounded-lg bg-white p-12 text-center shadow-sm">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-8 w-8 text-green-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-gray-800">All Good!</h3>
            <p class="mt-1 text-sm text-gray-500">No stock alerts right now. All products are above their minimum stock.</p>
        </div>
    @endif

@endsection
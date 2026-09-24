@extends('layouts.admin')

@section('title', 'Adjust Stock')
@section('page-title', 'Adjust Stock')

@section('content')

    <div class="mx-auto max-w-2xl space-y-4">

        {{-- Product info --}}
        <div class="rounded-lg bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-800">{{ $product->name }}</h2>
            <p class="mt-1 text-sm text-gray-500">SKU: {{ $product->sku }}@if($product->model) · {{ $product->model }}@endif</p>

            <div class="mt-4 grid grid-cols-2 gap-4">
                <div class="rounded-md bg-gray-50 p-3">
                    <p class="text-xs uppercase tracking-wider text-gray-500">Current Stock</p>
                    <p class="mt-1 text-2xl font-bold text-gray-800">{{ $product->quantity }}</p>
                </div>
                <div class="rounded-md bg-gray-50 p-3">
                    <p class="text-xs uppercase tracking-wider text-gray-500">Minimum Stock</p>
                    <p class="mt-1 text-2xl font-bold text-gray-800">{{ $product->minimum_stock }}</p>
                </div>
            </div>
        </div>

        {{-- Adjustment form --}}
        <div class="rounded-lg bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('admin.stock.adjust', $product) }}">
                @csrf

                <div class="mb-4">
                    <label for="type" class="mb-1 block text-sm font-medium text-gray-700">
                        Reason <span class="text-red-500">*</span>
                    </label>
                    <select name="type" id="type" required
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option value="adjustment" @selected(old('type', 'adjustment') === 'adjustment')>Adjustment (correction)</option>
                        <option value="damage"     @selected(old('type') === 'damage')>Damage (broken/lost)</option>
                        <option value="return"     @selected(old('type') === 'return')>Return (customer returned)</option>
                    </select>
                    @error('type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label for="mode" class="mb-1 block text-sm font-medium text-gray-700">
                        Operation <span class="text-red-500">*</span>
                    </label>
                    <select name="mode" id="mode" required
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option value="add"      @selected(old('mode', 'add') === 'add')>Add to stock</option>
                        <option value="subtract" @selected(old('mode') === 'subtract')>Subtract from stock</option>
                        <option value="set"      @selected(old('mode') === 'set')>Set to exact value</option>
                    </select>
                    @error('mode') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label for="quantity" class="mb-1 block text-sm font-medium text-gray-700">
                        Quantity <span class="text-red-500">*</span>
                    </label>
                    <input type="number" min="1" name="quantity" id="quantity" value="{{ old('quantity', 1) }}" required
                           class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    @error('quantity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label for="notes" class="mb-1 block text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" id="notes" rows="3" placeholder="Reason for adjustment..."
                              class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                    @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('admin.stock.index') }}"
                       class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        Apply Adjustment
                    </button>
                </div>
            </form>
        </div>

    </div>

@endsection
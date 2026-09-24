@extends('layouts.admin')

@section('title', 'Add Product')
@section('page-title', 'Add Product')

@section('content')

    <div class="mx-auto max-w-4xl rounded-lg bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
            @csrf

            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-gray-500">Basic Info</h3>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="name" class="mb-1 block text-sm font-medium text-gray-700">Product Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                           class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="sku" class="mb-1 block text-sm font-medium text-gray-700">SKU <span class="text-red-500">*</span></label>
                    <input type="text" name="sku" id="sku" value="{{ old('sku') }}" required
                           class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-gray-500">Unique code, e.g. BAT-IP11-001</p>
                    @error('sku') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="model" class="mb-1 block text-sm font-medium text-gray-700">Phone Model</label>
                    <input type="text" name="model" id="model" value="{{ old('model') }}"
                           class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                           placeholder="e.g. iPhone 11">
                    @error('model') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="category_id" class="mb-1 block text-sm font-medium text-gray-700">Category <span class="text-red-500">*</span></label>
                    <select name="category_id" id="category_id" required
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option value="">— Select category —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="brand_id" class="mb-1 block text-sm font-medium text-gray-700">Brand <span class="text-red-500">*</span></label>
                    <select name="brand_id" id="brand_id" required
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option value="">— Select brand —</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" @selected(old('brand_id') == $brand->id)>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                    @error('brand_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="supplier_id" class="mb-1 block text-sm font-medium text-gray-700">Preferred Supplier</label>
                    <select name="supplier_id" id="supplier_id"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option value="">— None —</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" @selected(old('supplier_id') == $sup->id)>
                                {{ $sup->name }} @if($sup->company) ({{ $sup->company }}) @endif
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="mb-1 block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="3"
                              class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">{{ old('description') }}</textarea>
                    @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <h3 class="mb-3 mt-8 text-sm font-semibold uppercase tracking-wider text-gray-500">Pricing & Stock</h3>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="purchase_price" class="mb-1 block text-sm font-medium text-gray-700">Purchase Price (AFN) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0" name="purchase_price" id="purchase_price" value="{{ old('purchase_price', 0) }}" required
                           class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    @error('purchase_price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="selling_price" class="mb-1 block text-sm font-medium text-gray-700">Selling Price (AFN) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0" name="selling_price" id="selling_price" value="{{ old('selling_price', 0) }}" required
                           class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    @error('selling_price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="quantity" class="mb-1 block text-sm font-medium text-gray-700">Opening Quantity <span class="text-red-500">*</span></label>
                    <input type="number" min="0" name="quantity" id="quantity" value="{{ old('quantity', 0) }}" required
                           class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    @error('quantity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="minimum_stock" class="mb-1 block text-sm font-medium text-gray-700">Minimum Stock Alert <span class="text-red-500">*</span></label>
                    <input type="number" min="0" name="minimum_stock" id="minimum_stock" value="{{ old('minimum_stock', 5) }}" required
                           class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    @error('minimum_stock') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <h3 class="mb-3 mt-8 text-sm font-semibold uppercase tracking-wider text-gray-500">Image & Status</h3>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="image" class="mb-1 block text-sm font-medium text-gray-700">Product Image</label>
                    <input type="file" name="image" id="image" accept="image/*"
                           class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm file:mr-3 file:rounded file:border-0 file:bg-indigo-50 file:px-3 file:py-1 file:text-xs file:font-medium file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="mt-1 text-xs text-gray-500">JPG, PNG or WEBP. Max 2 MB.</p>
                    @error('image') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="status" class="mb-1 block text-sm font-medium text-gray-700">Status <span class="text-red-500">*</span></label>
                    <select name="status" id="status" required
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                    </select>
                    @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-8 flex items-center justify-end gap-3">
                <a href="{{ route('admin.products.index') }}"
                   class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Save Product
                </button>
            </div>
        </form>
    </div>

@endsection
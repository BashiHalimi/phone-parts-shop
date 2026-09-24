@extends('layouts.admin')

@section('title', 'Products')
@section('page-title', 'Products')

@section('content')

    {{-- Filters --}}
    <div class="mb-4 rounded-lg bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.products.index') }}"
              class="grid grid-cols-1 gap-3 md:grid-cols-4">

            <div class="md:col-span-2">
                <input
                    type="text"
                    name="search"
                    value="{{ $search ?? '' }}"
                    placeholder="Search name, SKU, or model..."
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                >
            </div>

            <div>
                <select name="category_id"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected((string)$categoryFilter === (string)$cat->id)>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="stock"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">All Stock</option>
                    <option value="low" @selected($stockFilter === 'low')>Low Stock</option>
                    <option value="out" @selected($stockFilter === 'out')>Out of Stock</option>
                </select>
            </div>

            <div class="md:col-span-4 flex items-center justify-between gap-3">
                <div class="flex gap-2">
                    <button type="submit"
                            class="rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                        Apply Filters
                    </button>
                    <a href="{{ route('admin.products.index') }}"
                       class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Reset
                    </a>
                </div>

                <a href="{{ route('admin.products.create') }}"
                   class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    + Add Product
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
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Image</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Name / SKU</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Category</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Brand</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Buy</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Sell</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600">Stock</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($products as $product)
                    {{-- ... existing rows ... --}}
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center">
                                <div class="mx-auto flex max-w-sm flex-col items-center">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-7 w-7 text-gray-400">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                        </svg>
                                    </div>
                                    <p class="mt-3 text-sm font-medium text-gray-700">No products yet</p>
                                    <p class="mt-1 text-xs text-gray-500">Add your first product to start selling.</p>
                                    <a href="{{ route('admin.products.create') }}"
                                    class="mt-4 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                        + Add Product
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>

    {{-- Delete Modal --}}
    <div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
            <h3 class="text-lg font-semibold text-gray-800">Delete Product</h3>
            <p class="mt-2 text-sm text-gray-600">
                Are you sure you want to delete
                <strong id="deleteProductName" class="text-gray-800"></strong>?
                This action cannot be undone.
            </p>
            <form id="deleteForm" method="POST" action="" class="mt-6 flex items-center justify-end gap-3">
                @csrf
                @method('DELETE')
                <button type="button" onclick="closeDeleteModal()"
                        class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                    Yes, Delete
                </button>
            </form>
        </div>
    </div>

    <script>
        function openDeleteModal(id, name) {
            const modal = document.getElementById('deleteModal');
            const form  = document.getElementById('deleteForm');
            const label = document.getElementById('deleteProductName');
            form.action = '/admin/products/' + id;
            label.textContent = name;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        document.getElementById('deleteModal')?.addEventListener('click', function (e) {
            if (e.target === this) closeDeleteModal();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeDeleteModal();
        });
    </script>

@endsection
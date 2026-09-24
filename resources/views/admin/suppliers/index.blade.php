@extends('layouts.admin')

@section('title', 'Suppliers')
@section('page-title', 'Suppliers')

@section('content')

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('admin.suppliers.index') }}" class="flex w-full sm:max-w-sm">
            <input
                type="text"
                name="search"
                value="{{ $search ?? '' }}"
                placeholder="Search name, company, phone, email..."
                class="w-full rounded-l-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
            >
            <button type="submit" class="rounded-r-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                Search
            </button>
        </form>

        <a href="{{ route('admin.suppliers.create') }}"
           class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
            + Add Supplier
        </a>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">#</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Name</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Company</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Phone</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Email</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Balance</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($suppliers as $supplier)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500">{{ $supplier->id }}</td>
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $supplier->name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $supplier->company ?: '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $supplier->phone }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $supplier->email ?: '—' }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-800">
                                {{ number_format((float) $supplier->balance, 2) }} AFN
                            </td>
                            <td class="px-4 py-3">
                                @if($supplier->status === 'active')
                                    <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">Active</span>
                                @else
                                    <span class="rounded-full bg-gray-200 px-2 py-0.5 text-xs font-medium text-gray-700">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.suppliers.edit', $supplier) }}"
                                   class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                    Edit
                                </a>
                                <button
                                    type="button"
                                    class="ml-3 text-sm font-medium text-red-600 hover:text-red-800"
                                    onclick="openDeleteModal({{ $supplier->id }}, '{{ addslashes($supplier->name) }}')"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                No suppliers found.
                                <a href="{{ route('admin.suppliers.create') }}" class="text-indigo-600 hover:underline">Add the first one.</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $suppliers->links() }}
    </div>

    {{-- Delete Confirmation Modal --}}
    <div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
            <h3 class="text-lg font-semibold text-gray-800">Delete Supplier</h3>
            <p class="mt-2 text-sm text-gray-600">
                Are you sure you want to delete
                <strong id="deleteSupplierName" class="text-gray-800"></strong>?
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
            const label = document.getElementById('deleteSupplierName');

            form.action = '/admin/suppliers/' + id;
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
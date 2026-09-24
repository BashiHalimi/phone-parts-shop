@extends('layouts.admin')

@section('title', 'Customers')
@section('page-title', 'Customers')

@section('content')

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('admin.customers.index') }}" class="flex w-full sm:max-w-sm">
            <input type="text" name="search" value="{{ $search ?? '' }}"
                   placeholder="Search name, phone, or email..."
                   class="w-full rounded-l-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            <button type="submit"
                    class="rounded-r-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                Search
            </button>
        </form>

        <a href="{{ route('admin.customers.create') }}"
           class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
            + Add Customer
        </a>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">#</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Name</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Phone</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Email</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Address</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Balance</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500">{{ $customer->id }}</td>
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $customer->name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $customer->phone }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $customer->email ?: '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ \Illuminate\Support\Str::limit($customer->address, 40) ?: '—' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if((float)$customer->balance > 0)
                                    <span class="font-medium text-red-700">{{ number_format((float)$customer->balance, 2) }} AFN</span>
                                @elseif((float)$customer->balance < 0)
                                    <span class="font-medium text-green-700">{{ number_format((float)$customer->balance, 2) }} AFN</span>
                                @else
                                    <span class="text-gray-500">0.00 AFN</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($customer->status === 'active')
                                    <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">Active</span>
                                @else
                                    <span class="rounded-full bg-gray-200 px-2 py-0.5 text-xs font-medium text-gray-700">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.customers.edit', $customer) }}"
                                   class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Edit</a>
                                <button type="button"
                                        class="ml-3 text-sm font-medium text-red-600 hover:text-red-800"
                                        onclick="openDeleteModal({{ $customer->id }}, '{{ addslashes($customer->name) }}')">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <div class="mx-auto flex max-w-sm flex-col items-center">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-7 w-7 text-gray-400">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                        </svg>
                                    </div>
                                    <p class="mt-3 text-sm font-medium text-gray-700">No customers yet</p>
                                    <p class="mt-1 text-xs text-gray-500">Add your first customer to get started.</p>
                                    <a href="{{ route('admin.customers.create') }}"
                                    class="mt-4 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                        + Add Customer
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
        {{ $customers->links() }}
    </div>

    {{-- Delete Confirmation Modal --}}
    <div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
            <h3 class="text-lg font-semibold text-gray-800">Delete Customer</h3>
            <p class="mt-2 text-sm text-gray-600">
                Are you sure you want to delete
                <strong id="deleteCustomerName" class="text-gray-800"></strong>?
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
            document.getElementById('deleteForm').action = '/admin/customers/' + id;
            document.getElementById('deleteCustomerName').textContent = name;
            document.getElementById('deleteModal').classList.remove('hidden');
            document.getElementById('deleteModal').classList.add('flex');
        }
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.getElementById('deleteModal').classList.remove('flex');
        }
        document.getElementById('deleteModal')?.addEventListener('click', function (e) {
            if (e.target === this) closeDeleteModal();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeDeleteModal();
        });
    </script>

@endsection
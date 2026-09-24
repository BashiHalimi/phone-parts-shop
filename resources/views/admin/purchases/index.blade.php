@extends('layouts.admin')

@section('title', 'Purchases')
@section('page-title', 'Purchases')

@section('content')

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('admin.purchases.index') }}"
              class="flex w-full flex-col gap-2 sm:flex-row sm:max-w-xl">
            <input type="text" name="search" value="{{ $search ?? '' }}"
                   placeholder="Search invoice or supplier..."
                   class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            <select name="supplier_id"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:w-48">
                <option value="">All Suppliers</option>
                @foreach($suppliers as $sup)
                    <option value="{{ $sup->id }}" @selected((string)$supplierFilter === (string)$sup->id)>{{ $sup->name }}</option>
                @endforeach
            </select>
            <button type="submit"
                    class="rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                Filter
            </button>
        </form>

        <a href="{{ route('admin.purchases.create') }}"
           class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
            + New Purchase
        </a>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Invoice</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Supplier</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Date</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Total</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Paid</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Balance</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($purchases as $purchase)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $purchase->invoice_no }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $purchase->supplier?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $purchase->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-800">
                                {{ number_format((float)$purchase->total, 2) }} AFN
                            </td>
                            <td class="px-4 py-3 text-right text-gray-600">
                                {{ number_format((float)$purchase->paid, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if((float)$purchase->balance > 0)
                                    <span class="font-medium text-red-700">{{ number_format((float)$purchase->balance, 2) }}</span>
                                @else
                                    <span class="text-green-700">0.00</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $statusColors = [
                                        'pending'   => 'bg-amber-100 text-amber-700',
                                        'received'  => 'bg-green-100 text-green-700',
                                        'cancelled' => 'bg-gray-200 text-gray-700',
                                    ];
                                @endphp
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $statusColors[$purchase->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ ucfirst($purchase->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.purchases.show', $purchase) }}"
                                   class="text-sm font-medium text-gray-700 hover:text-gray-900">View</a>
                                <button type="button"
                                        class="ml-3 text-sm font-medium text-red-600 hover:text-red-800"
                                        onclick="openDeleteModal({{ $purchase->id }}, '{{ $purchase->invoice_no }}')">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                No purchases yet.
                                <a href="{{ route('admin.purchases.create') }}" class="text-indigo-600 hover:underline">Record the first one.</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $purchases->links() }}
    </div>

    {{-- Delete Modal --}}
    <div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
            <h3 class="text-lg font-semibold text-gray-800">Delete Purchase</h3>
            <p class="mt-2 text-sm text-gray-600">
                Deleting purchase <strong id="deleteInvNo" class="text-gray-800"></strong>
                will <span class="font-semibold text-red-700">reverse the stock</span> that was added.
                This cannot be undone.
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
                    Yes, Delete & Reverse
                </button>
            </form>
        </div>
    </div>

    <script>
        function openDeleteModal(id, inv) {
            document.getElementById('deleteForm').action = '/admin/purchases/' + id;
            document.getElementById('deleteInvNo').textContent = inv;
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
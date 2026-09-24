@extends('layouts.admin')

@section('title', 'Sales')
@section('page-title', 'Sales')

@section('content')

    <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <form method="GET" action="{{ route('admin.sales.index') }}"
              class="grid w-full grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-5">
            <input type="text" name="search" value="{{ $search ?? '' }}"
                   placeholder="Invoice or customer..."
                   class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            <select name="customer_id"
                    class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <option value="">All Customers</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}" @selected((string)$customer === (string)$c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}"
                   class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            <input type="date" name="date_to" value="{{ $dateTo ?? '' }}"
                   class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            <div class="flex gap-2">
                <button type="submit"
                        class="flex-1 rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    Filter
                </button>
                <a href="{{ route('admin.sales.index') }}"
                   class="flex-1 rounded-md border border-gray-300 px-4 py-2 text-center text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="mb-4 flex justify-end">
        <a href="{{ route('admin.sales.create') }}"
           class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
            + New Sale
        </a>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Invoice</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Customer</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Date</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Total</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Paid</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Balance</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($sales as $sale)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $sale->invoice_no }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-800">
                                {{ number_format((float)$sale->total, 2) }} AFN
                            </td>
                            <td class="px-4 py-3 text-right text-gray-600">
                                {{ number_format((float)$sale->paid, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if((float)$sale->balance > 0)
                                    <span class="font-medium text-red-700">{{ number_format((float)$sale->balance, 2) }}</span>
                                @else
                                    <span class="text-green-700">0.00</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $statusColors = [
                                        'completed' => 'bg-green-100 text-green-700',
                                        'pending'   => 'bg-amber-100 text-amber-700',
                                        'cancelled' => 'bg-gray-200 text-gray-700',
                                    ];
                                @endphp
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $statusColors[$sale->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ ucfirst($sale->status) }}
                                </span>
                            </td>
                            
                            <td class="px-4 py-3 text-right whitespace-nowrap">
    <a href="{{ route('admin.sales.show', $sale) }}"
       class="text-sm font-medium text-gray-700 hover:text-gray-900">View</a>

    @if(auth()->user()->isAdmin())
        <button type="button"
                class="ml-3 text-sm font-medium text-red-600 hover:text-red-800"
                onclick="openDeleteModal({{ $sale->id }}, '{{ $sale->invoice_no }}')">
            Delete
        </button>
    @endif
</td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <div class="mx-auto flex max-w-sm flex-col items-center">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-7 w-7 text-gray-400">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <p class="mt-3 text-sm font-medium text-gray-700">No sales yet</p>
                                    <p class="mt-1 text-xs text-gray-500">Record your first sale or use POS.</p>
                                    <div class="mt-4 flex gap-2">
                                        <a href="{{ route('admin.sales.pos') }}"
                                        class="rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                                            Open POS
                                        </a>
                                        <a href="{{ route('admin.sales.create') }}"
                                        class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                            + New Sale
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $sales->links() }}</div>

    {{-- Delete Modal --}}
    <div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
            <h3 class="text-lg font-semibold text-gray-800">Delete Sale</h3>
            <p class="mt-2 text-sm text-gray-600">
                Deleting sale <strong id="deleteInvNo" class="text-gray-800"></strong>
                will <span class="font-semibold text-red-700">restore stock</span> and reverse the customer balance if any.
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
                    Yes, Delete & Restore
                </button>
            </form>
        </div>
    </div>

    <script>
        function openDeleteModal(id, inv) {
            document.getElementById('deleteForm').action = '/admin/sales/' + id;
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
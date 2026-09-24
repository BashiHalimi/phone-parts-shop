@extends('layouts.admin')

@section('title', 'Expenses')
@section('page-title', 'Expenses')

@section('content')

    {{-- Summary cards --}}
    <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">This Month</p>
            <p class="mt-2 text-2xl font-bold text-red-600">{{ number_format((float)$totalThisMonth, 2) }}</p>
            <p class="text-xs text-gray-500">AFN · {{ $countThisMonth }} entries</p>
        </div>
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">All-Time Total</p>
            <p class="mt-2 text-2xl font-bold text-gray-800">{{ number_format((float)$totalAllTime, 2) }}</p>
            <p class="text-xs text-gray-500">AFN</p>
        </div>
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Categories</p>
            <p class="mt-2 text-2xl font-bold text-gray-800">{{ count($categories) }}</p>
            <p class="text-xs text-gray-500">available</p>
        </div>
    </div>

    {{-- Filters + Add button --}}
    <div class="mb-4 rounded-lg bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.expenses.index') }}"
              class="grid grid-cols-1 gap-3 md:grid-cols-5">
            <div class="md:col-span-2">
                <input type="text" name="search" value="{{ $search ?? '' }}"
                       placeholder="Search title or description..."
                       class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
            <div>
                <select name="category"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}" @selected($category === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}"
                       placeholder="From"
                       class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
            <div>
                <input type="date" name="date_to" value="{{ $dateTo ?? '' }}"
                       placeholder="To"
                       class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
            <div class="md:col-span-5 flex items-center justify-between gap-3">
                <div class="flex gap-2">
                    <button type="submit"
                            class="rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                        Apply Filters
                    </button>
                    <a href="{{ route('admin.expenses.index') }}"
                       class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Reset
                    </a>
                </div>
                <a href="{{ route('admin.expenses.create') }}"
                   class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    + Add Expense
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
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Title</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Category</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Amount</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Recorded By</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($expenses as $expense)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $expense->date->format('Y-m-d') }}</td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800">{{ $expense->title }}</p>
                                @if($expense->description)
                                    <p class="text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($expense->description, 60) }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">
                                    {{ $expense->categoryLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-red-700">
                                {{ number_format((float)$expense->amount, 2) }} AFN
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $expense->user?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.expenses.edit', $expense) }}"
                                   class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Edit</a>
                                <button type="button"
                                        class="ml-3 text-sm font-medium text-red-600 hover:text-red-800"
                                        onclick="openDeleteModal({{ $expense->id }}, '{{ addslashes($expense->title) }}')">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                No expenses recorded.
                                <a href="{{ route('admin.expenses.create') }}" class="text-indigo-600 hover:underline">Add the first one.</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $expenses->links() }}</div>

    {{-- Delete Modal --}}
    <div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
            <h3 class="text-lg font-semibold text-gray-800">Delete Expense</h3>
            <p class="mt-2 text-sm text-gray-600">
                Delete <strong id="deleteExpenseTitle" class="text-gray-800"></strong>? This action cannot be undone.
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
        function openDeleteModal(id, title) {
            document.getElementById('deleteForm').action = '/admin/expenses/' + id;
            document.getElementById('deleteExpenseTitle').textContent = title;
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
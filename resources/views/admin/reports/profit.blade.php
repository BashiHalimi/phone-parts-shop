@extends('layouts.admin')

@section('title', 'Profit Report')
@section('page-title', 'Profit Report')

@section('content')

    <div class="mb-4 rounded-lg bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.reports.profit') }}"
              class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">From</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}"
                       class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600">To</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}"
                       class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit"
                        class="flex-1 rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    Apply
                </button>
                <a href="{{ route('admin.reports.profit') }}"
                   class="flex-1 rounded-md border border-gray-300 px-4 py-2 text-center text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Profit breakdown --}}
    <div class="rounded-lg bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-500">Profit & Loss Summary</h3>

        <div class="space-y-2 text-sm">
            <div class="flex justify-between border-b border-gray-100 pb-2">
                <span class="text-gray-600">Revenue (Sales Total)</span>
                <span class="font-medium text-gray-900">{{ number_format($revenue, 2) }} AFN</span>
            </div>
            <div class="flex justify-between border-b border-gray-100 pb-2">
                <span class="text-gray-600">Cost of Goods Sold (COGS)</span>
                <span class="font-medium text-red-700">− {{ number_format($cogs, 2) }} AFN</span>
            </div>
            <div class="flex justify-between border-b border-gray-200 pb-2 pt-1">
                <span class="font-semibold text-gray-800">Gross Profit</span>
                <span class="text-base font-bold {{ $grossProfit >= 0 ? 'text-green-700' : 'text-red-700' }}">
                    {{ number_format($grossProfit, 2) }} AFN
                </span>
            </div>
            <div class="flex justify-between border-b border-gray-100 pb-2">
                <span class="text-gray-600">Operating Expenses</span>
                <span class="font-medium text-red-700">− {{ number_format($expenses, 2) }} AFN</span>
            </div>
            <div class="flex justify-between border-t border-gray-300 pt-3">
                <span class="text-base font-semibold text-gray-800">Net Profit</span>
                <span class="text-xl font-bold {{ $netProfit >= 0 ? 'text-green-700' : 'text-red-700' }}">
                    {{ number_format($netProfit, 2) }} AFN
                </span>
            </div>
        </div>

        <p class="mt-4 text-xs text-gray-500">
            Note: COGS is calculated using each product's <em>current</em> purchase price. This approximates actual cost for
            shops where prices don't fluctuate frequently.
        </p>
    </div>

    {{-- Expenses by category --}}
    <div class="mt-4 rounded-lg bg-white p-6 shadow-sm">
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-gray-500">Expenses by Category</h3>
        @if($expensesByCategory->isNotEmpty())
            <table class="min-w-full text-sm">
                <thead class="border-b border-gray-200">
                    <tr class="text-left text-gray-500">
                        <th class="py-2 font-medium">Category</th>
                        <th class="py-2 text-right font-medium">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($expensesByCategory as $row)
                        <tr>
                            <td class="py-2 text-gray-700">{{ ucfirst($row->category) }}</td>
                            <td class="py-2 text-right font-medium text-gray-800">{{ number_format((float)$row->total, 2) }} AFN</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-sm text-gray-500">No expenses recorded in this period.</p>
        @endif
    </div>

@endsection
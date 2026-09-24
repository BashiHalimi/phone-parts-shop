@extends('layouts.admin')

@section('title', 'Payments')
@section('page-title', 'Payments')

@section('content')

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('admin.payments.index') }}"
              class="flex w-full flex-col gap-2 sm:flex-row sm:max-w-xl">
            <input type="text" name="search" value="{{ $search ?? '' }}"
                   placeholder="Search reference, notes, or invoice..."
                   class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            <select name="method"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:w-40">
                <option value="">All Methods</option>
                <option value="cash"  @selected($method === 'cash')>Cash</option>
                <option value="bank"  @selected($method === 'bank')>Bank</option>
                <option value="other" @selected($method === 'other')>Other</option>
            </select>
            <button type="submit"
                    class="rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                Filter
            </button>
        </form>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Date</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Type</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Invoice</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Amount</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Method</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Reference</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($payments as $payment)
                        @php
                            $payable = $payment->payable;
                            $isSale  = $payable instanceof \App\Models\Sale;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3">
                                @if($isSale)
                                    <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700">Sale</span>
                                @else
                                    <span class="rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-700">Purchase</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-800">
                                @if($payable)
                                    @if($isSale)
                                        <a href="{{ route('admin.sales.show', $payable) }}" class="hover:text-indigo-600">
                                            {{ $payable->invoice_no }}
                                        </a>
                                    @else
                                        <a href="{{ route('admin.purchases.show', $payable) }}" class="hover:text-indigo-600">
                                            {{ $payable->invoice_no }}
                                        </a>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-green-700">
                                {{ number_format((float)$payment->amount, 2) }} AFN
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ ucfirst($payment->method) }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $payment->reference ?: '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $payment->user?->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">No payments recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $payments->links() }}</div>

@endsection
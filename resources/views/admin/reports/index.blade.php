@extends('layouts.admin')

@section('title', 'Reports')
@section('page-title', 'Reports')

@section('content')

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

        <a href="{{ route('admin.reports.sales') }}"
           class="block rounded-lg bg-white p-6 shadow-sm transition hover:shadow-md">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                </svg>
            </div>
            <h3 class="mt-4 text-base font-semibold text-gray-800">Sales Report</h3>
            <p class="mt-1 text-sm text-gray-500">Daily, weekly, monthly sales with filters.</p>
        </a>

        <a href="{{ route('admin.reports.purchases') }}"
           class="block rounded-lg bg-white p-6 shadow-sm transition hover:shadow-md">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-purple-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                </svg>
            </div>
            <h3 class="mt-4 text-base font-semibold text-gray-800">Purchase Report</h3>
            <p class="mt-1 text-sm text-gray-500">Purchases by supplier and date.</p>
        </a>

        <a href="{{ route('admin.reports.stock') }}"
           class="block rounded-lg bg-white p-6 shadow-sm transition hover:shadow-md">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                </svg>
            </div>
            <h3 class="mt-4 text-base font-semibold text-gray-800">Stock Report</h3>
            <p class="mt-1 text-sm text-gray-500">Current, low, and out-of-stock items.</p>
        </a>

        <a href="{{ route('admin.reports.profit') }}"
           class="block rounded-lg bg-white p-6 shadow-sm transition hover:shadow-md">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 text-green-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="mt-4 text-base font-semibold text-gray-800">Profit Report</h3>
            <p class="mt-1 text-sm text-gray-500">Revenue, cost, expenses, and net profit.</p>
        </a>

    </div>

@endsection
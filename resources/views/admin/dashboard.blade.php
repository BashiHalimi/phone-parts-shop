@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

    {{-- Welcome --}}
    <div class="mb-4 rounded-lg bg-white p-5 shadow-sm">
        <h2 class="text-xl font-semibold text-gray-800">
            Welcome back, {{ auth()->user()->name }} 👋
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            Here's what's happening in your shop today.
        </p>
    </div>

    {{-- Row 1: Today + Month stat cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Today's Sales</p>
            <p class="mt-2 text-2xl font-bold text-gray-800">
                {{ number_format((float)$todaySales, 2) }}
            </p>
            <p class="text-xs text-gray-500">AFN · {{ $todaySalesCount }} sale(s)</p>
        </div>

        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Today's Profit</p>
            <p class="mt-2 text-2xl font-bold {{ $todayProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ number_format($todayProfit, 2) }}
            </p>
            <p class="text-xs text-gray-500">AFN (gross)</p>
        </div>

        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Today's Purchases</p>
            <p class="mt-2 text-2xl font-bold text-gray-800">
                {{ number_format((float)$todayPurchases, 2) }}
            </p>
            <p class="text-xs text-gray-500">AFN</p>
        </div>

        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">This Month's Sales</p>
            <p class="mt-2 text-2xl font-bold text-gray-800">
                {{ number_format((float)$monthSales, 2) }}
            </p>
            <p class="text-xs text-gray-500">AFN</p>
        </div>
    </div>

    {{-- Row 2: Inventory + Counts --}}
    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Total Products</p>
            <p class="mt-2 text-2xl font-bold text-gray-800">{{ $totalProducts }}</p>
        </div>

        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Low Stock</p>
            <p class="mt-2 text-2xl font-bold text-amber-600">{{ $lowStockCount }}</p>
        </div>

        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Out of Stock</p>
            <p class="mt-2 text-2xl font-bold text-red-600">{{ $outOfStockCount }}</p>
        </div>

        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Customers / Suppliers</p>
            <p class="mt-2 text-2xl font-bold text-gray-800">
                {{ $totalCustomers }} / {{ $totalSuppliers }}
            </p>
        </div>
    </div>

    {{-- Monthly summary --}}
    <div class="mt-4 rounded-lg bg-white p-5 shadow-sm">
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-gray-500">This Month at a Glance</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <p class="text-xs text-gray-500">Sales</p>
                <p class="mt-1 text-lg font-semibold text-gray-800">{{ number_format((float)$monthSales, 2) }} AFN</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Purchases</p>
                <p class="mt-1 text-lg font-semibold text-gray-800">{{ number_format((float)$monthPurchases, 2) }} AFN</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Expenses</p>
                <p class="mt-1 text-lg font-semibold text-red-700">{{ number_format((float)$monthExpenses, 2) }} AFN</p>
            </div>
        </div>
    </div>

    {{-- Chart --}}
    <div class="mt-4 rounded-lg bg-white p-5 shadow-sm">
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-gray-500">Sales & Purchases — Last 30 Days</h3>
        <div style="height: 280px;">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    {{-- Two columns: Top products + Low stock alerts --}}
    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">

       {{-- Top Products --}}
<div class="rounded-lg bg-white p-5 shadow-sm">
    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-gray-500">Top Selling Products (This Month)</h3>

    @if(count($topProducts) > 0)
        <table class="min-w-full text-sm">
            <thead class="border-b border-gray-200">
                <tr class="text-left text-gray-500">
                    <th class="py-2 font-medium">Product</th>
                    <th class="py-2 text-right font-medium">Qty</th>
                    <th class="py-2 text-right font-medium">Revenue</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($topProducts as $tp)
                    <tr>
                        <td class="py-2 font-medium text-gray-800">
                            {{ $tp['product_name'] ?? '—' }}
                            <p class="text-xs text-gray-500">{{ $tp['product_sku'] }}</p>
                        </td>
                        <td class="py-2 text-right text-gray-700">{{ $tp['total_qty'] }}</td>
                        <td class="py-2 text-right font-medium text-gray-800">
                            {{ number_format($tp['total_revenue'], 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-sm text-gray-500">No sales recorded this month yet.</p>
    @endif
</div>
        

        {{-- Low Stock Alerts --}}
        {{-- Low Stock Alerts --}}
<div class="rounded-lg bg-white p-5 shadow-sm">
    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-gray-500">Low Stock Alerts</h3>

    @if(count($lowStockProducts) > 0)
        <ul class="divide-y divide-gray-100">
            @foreach($lowStockProducts as $p)
                <li class="flex items-center justify-between py-2">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-gray-800">{{ $p['name'] }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $p['sku'] }} @if($p['category_name']) · {{ $p['category_name'] }} @endif
                        </p>
                    </div>
                    <div class="ml-3 shrink-0 text-right">
                        @if($p['quantity'] <= 0)
                            <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">Out</span>
                        @else
                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">
                                {{ $p['quantity'] }} left
                            </span>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
        <div class="mt-3 text-right">
            <a href="{{ route('admin.stock.index', ['filter' => 'low']) }}"
               class="text-xs font-medium text-indigo-600 hover:underline">View all low stock →</a>
        </div>
    @else
        <p class="text-sm text-gray-500">All products are above their minimum stock. 👍</p>
    @endif
</div>
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartData = {!! json_encode($salesChartData) !!};

            const labels = chartData.map(d => d.label);
            const salesData = chartData.map(d => d.sales);
            const purchData = chartData.map(d => d.purch);

            const ctx = document.getElementById('salesChart').getContext('2d');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Sales',
                            data: salesData,
                            borderColor: 'rgb(79, 70, 229)',
                            backgroundColor: 'rgba(79, 70, 229, 0.1)',
                            tension: 0.3,
                            fill: true,
                            borderWidth: 2,
                            pointRadius: 2,
                        },
                        {
                            label: 'Purchases',
                            data: purchData,
                            borderColor: 'rgb(220, 38, 38)',
                            backgroundColor: 'rgba(220, 38, 38, 0.05)',
                            tension: 0.3,
                            fill: false,
                            borderWidth: 2,
                            pointRadius: 2,
                            borderDash: [4, 3],
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return context.dataset.label + ': ' + context.parsed.y.toFixed(2) + ' AFN';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value) {
                                    return value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>

@endsection
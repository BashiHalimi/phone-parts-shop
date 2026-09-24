@extends('layouts.admin')

@section('title', 'New Purchase')
@section('page-title', 'New Purchase')

@section('content')

    <form method="POST" action="{{ route('admin.purchases.store') }}" id="purchaseForm">
        @csrf

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

            {{-- LEFT: Items --}}
            <div class="lg:col-span-2 space-y-4">

                {{-- Supplier --}}
                <div class="rounded-lg bg-white p-5 shadow-sm">
                    <label for="supplier_id" class="mb-1 block text-sm font-medium text-gray-700">
                        Supplier <span class="text-red-500">*</span>
                    </label>
                    <select name="supplier_id" id="supplier_id" required
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option value="">— Select supplier —</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" @selected(old('supplier_id') == $sup->id)>
                                {{ $sup->name }} @if($sup->company) ({{ $sup->company }}) @endif
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Items --}}
                <div class="rounded-lg bg-white p-5 shadow-sm">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500">Products</h3>
                        <button type="button" onclick="addRow()"
                                class="rounded-md bg-gray-800 px-3 py-1.5 text-xs font-medium text-white hover:bg-gray-700">
                            + Add Row
                        </button>
                    </div>

                    @error('items') <p class="mb-2 text-sm text-red-600">{{ $message }}</p> @enderror

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm" id="itemsTable">
                            <thead>
                                <tr class="border-b border-gray-200 text-left text-gray-600">
                                    <th class="py-2 pr-2 font-medium">Product</th>
                                    <th class="py-2 pr-2 font-medium w-24">Qty</th>
                                    <th class="py-2 pr-2 font-medium w-32">Buy Price</th>
                                    <th class="py-2 pr-2 font-medium w-32 text-right">Subtotal</th>
                                    <th class="py-2 font-medium w-10"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                {{-- Rows injected by JS --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Totals & Payment --}}
            <div class="space-y-4">

                <div class="rounded-lg bg-white p-5 shadow-sm">
                    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-gray-500">Payment</h3>

                    <div class="space-y-3">
                        <div>
                            <label for="discount" class="mb-1 block text-sm font-medium text-gray-700">Discount (AFN)</label>
                            <input type="number" step="0.01" min="0" name="discount" id="discount"
                                   value="{{ old('discount', 0) }}"
                                   class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                   oninput="recalc()">
                            @error('discount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="paid" class="mb-1 block text-sm font-medium text-gray-700">
                                Amount Paid <span class="text-red-500">*</span>
                            </label>
                            <input type="number" step="0.01" min="0" name="paid" id="paid"
                                   value="{{ old('paid', 0) }}"
                                   class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                   oninput="recalc()">
                            @error('paid') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="payment_method" class="mb-1 block text-sm font-medium text-gray-700">
                                Payment Method <span class="text-red-500">*</span>
                            </label>
                            <select name="payment_method" id="payment_method" required
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option value="cash" @selected(old('payment_method', 'cash') === 'cash')>Cash</option>
                                <option value="bank" @selected(old('payment_method') === 'bank')>Bank</option>
                                <option value="other" @selected(old('payment_method') === 'other')>Other</option>
                            </select>
                        </div>

                        <div>
                            <label for="status" class="mb-1 block text-sm font-medium text-gray-700">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select name="status" id="status" required
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option value="received" @selected(old('status', 'received') === 'received')>Received</option>
                                <option value="pending" @selected(old('status') === 'pending')>Pending</option>
                                <option value="cancelled" @selected(old('status') === 'cancelled')>Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-5 shadow-sm">
                    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-gray-500">Summary</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Subtotal</dt>
                            <dd class="font-medium text-gray-800"><span id="summarySubtotal">0.00</span> AFN</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Discount</dt>
                            <dd class="font-medium text-gray-800"><span id="summaryDiscount">0.00</span> AFN</dd>
                        </div>
                        <div class="flex justify-between border-t border-gray-200 pt-2">
                            <dt class="font-semibold text-gray-700">Total</dt>
                            <dd class="text-lg font-bold text-gray-900"><span id="summaryTotal">0.00</span> AFN</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Paid</dt>
                            <dd class="font-medium text-green-700"><span id="summaryPaid">0.00</span> AFN</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Balance</dt>
                            <dd class="font-medium text-red-700"><span id="summaryBalance">0.00</span> AFN</dd>
                        </div>
                    </dl>
                </div>

                <div class="flex gap-3">
                    <a href="{{ route('admin.purchases.index') }}"
                       class="flex-1 rounded-md border border-gray-300 px-4 py-2 text-center text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit"
                            class="flex-1 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        Save Purchase
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- Product data available to JS --}}
    <script>
        const PRODUCTS  = {!! json_encode($products) !!};
        const OLD_ITEMS = {!! json_encode(old('items', [])) !!};

        let rowCounter = 0;

        function productOptions(selectedId = '') {
            let opts = '<option value="">— Select product —</option>';
            PRODUCTS.forEach(function (p) {
                const sel = String(selectedId) === String(p.id) ? 'selected' : '';
                opts += '<option value="' + p.id + '" data-price="' + p.purchase_price + '" ' + sel + '>'
                     + p.name + ' (' + p.sku + ')</option>';
            });
            return opts;
        }

        function addRow(productId, qty, price) {
            productId = productId || '';
            qty       = qty || 1;
            price     = price !== undefined && price !== '' ? price : '';

            const tbody = document.getElementById('itemsBody');
            const tr = document.createElement('tr');
            tr.className = 'border-b border-gray-100';
            tr.innerHTML =
                '<td class="py-2 pr-2">' +
                    '<select name="items[' + rowCounter + '][product_id]" onchange="onProductChange(this)" required ' +
                            'class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">' +
                        productOptions(productId) +
                    '</select>' +
                '</td>' +
                '<td class="py-2 pr-2">' +
                    '<input type="number" min="1" name="items[' + rowCounter + '][quantity]" value="' + qty + '" oninput="recalc()" required ' +
                           'class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">' +
                '</td>' +
                '<td class="py-2 pr-2">' +
                    '<input type="number" step="0.01" min="0" name="items[' + rowCounter + '][purchase_price]" value="' + price + '" oninput="recalc()" required ' +
                           'class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">' +
                '</td>' +
                '<td class="py-2 pr-2 text-right font-medium text-gray-800">' +
                    '<span class="row-subtotal">0.00</span>' +
                '</td>' +
                '<td class="py-2 text-right">' +
                    '<button type="button" onclick="this.closest(\'tr\').remove(); recalc();" ' +
                            'class="rounded p-1 text-red-600 hover:bg-red-50">&times;</button>' +
                '</td>';

            tbody.appendChild(tr);
            rowCounter++;

            if (productId) {
                const sel = tr.querySelector('select');
                onProductChange(sel, true);
            }

            recalc();
        }

        function onProductChange(select, keepPrice) {
            const opt = select.options[select.selectedIndex];
            const price = opt.getAttribute('data-price') || '';
            const tr = select.closest('tr');
            const priceInput = tr.querySelector('input[name*="[purchase_price]"]');
            if (price && !keepPrice) {
                priceInput.value = price;
            }
            recalc();
        }

        function recalc() {
            const rows = document.querySelectorAll('#itemsBody tr');
            let subtotal = 0;

            rows.forEach(function (tr) {
                const qty   = parseFloat(tr.querySelector('input[name*="[quantity]"]').value) || 0;
                const price = parseFloat(tr.querySelector('input[name*="[purchase_price]"]').value) || 0;
                const line  = qty * price;
                tr.querySelector('.row-subtotal').textContent = line.toFixed(2);
                subtotal += line;
            });

            const discount = parseFloat(document.getElementById('discount').value) || 0;
            const total    = Math.max(0, subtotal - discount);

            let paid = parseFloat(document.getElementById('paid').value) || 0;
            if (paid > total) paid = total;
            const balance = Math.max(0, total - paid);

            document.getElementById('summarySubtotal').textContent = subtotal.toFixed(2);
            document.getElementById('summaryDiscount').textContent = discount.toFixed(2);
            document.getElementById('summaryTotal').textContent    = total.toFixed(2);
            document.getElementById('summaryPaid').textContent     = paid.toFixed(2);
            document.getElementById('summaryBalance').textContent  = balance.toFixed(2);
        }

        document.addEventListener('DOMContentLoaded', function () {
            if (OLD_ITEMS && OLD_ITEMS.length > 0) {
                OLD_ITEMS.forEach(function (item) {
                    addRow(item.product_id, item.quantity, item.purchase_price);
                });
            } else {
                addRow();
            }
            recalc();
        });
    </script>

@endsection
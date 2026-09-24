@extends('layouts.pos')

@section('title', 'POS')

@section('content')

    {{-- ============ LEFT: PRODUCTS ============ --}}
    <div class="flex min-w-0 flex-1 flex-col bg-gray-100">

        {{-- Top bar --}}
        <div class="flex h-16 items-center gap-3 border-b border-gray-200 bg-white px-4">
            <a href="{{ route('admin.sales.index') }}"
               class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                ← Back
            </a>
            <input
                type="text"
                id="searchBox"
                placeholder="Search products... (F2)"
                class="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                autofocus
            >
            <div class="hidden text-right text-xs text-gray-400 sm:block">
                <div>F2 Search · F4 Paid · F9 Complete</div>
            </div>
        </div>

        {{-- Product grid --}}
        <div class="flex-1 overflow-y-auto p-4">
            <div id="productGrid" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5"></div>
            <div id="noProducts" class="hidden py-12 text-center text-gray-500">No products found.</div>
        </div>
    </div>

    {{-- ============ RIGHT: CART ============ --}}
    <div class="flex w-full max-w-md shrink-0 flex-col border-l border-gray-200 bg-white">

        {{-- Header --}}
        <div class="flex h-16 items-center justify-between border-b border-gray-200 px-4">
            <h2 class="text-lg font-semibold text-gray-800">Current Sale</h2>
            <button type="button" onclick="clearCart()"
                    class="rounded-md border border-red-300 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50">
                Clear
            </button>
        </div>

        {{-- Customer --}}
        <div class="border-b border-gray-200 p-4">
            <label for="customer_id" class="mb-1 block text-xs font-medium uppercase tracking-wider text-gray-500">
                Customer
            </label>
            <select id="customer_id"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <option value="">Walk-in Customer</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->name }} @if($c->phone) — {{ $c->phone }} @endif</option>
                @endforeach
            </select>
        </div>

        {{-- Cart --}}
        <div class="flex-1 overflow-y-auto p-4">
            <div id="cartItems" class="space-y-2">
                <div class="py-8 text-center text-sm text-gray-400">
                    Cart is empty.<br>Click a product to add it.
                </div>
            </div>
        </div>

        {{-- Totals --}}
        <div class="border-t border-gray-200 bg-gray-50 p-4 space-y-3">
            <div class="space-y-1 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Subtotal</span>
                    <span class="font-medium text-gray-800"><span id="posSubtotal">0.00</span> AFN</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-500">Discount</span>
                    <input type="number" step="0.01" min="0" id="posDiscount" value="0"
                           class="w-24 rounded-md border border-gray-300 px-2 py-1 text-right text-sm focus:border-indigo-500 focus:outline-none">
                </div>
                <div class="flex justify-between border-t border-gray-200 pt-2">
                    <span class="font-semibold text-gray-700">Total</span>
                    <span class="text-lg font-bold text-gray-900"><span id="posTotal">0.00</span> AFN</span>
                </div>
            </div>

            <div>
                <label for="posPaid" class="mb-1 block text-xs font-medium uppercase tracking-wider text-gray-500">
                    Amount Paid
                </label>
                <input type="number" step="0.01" min="0" id="posPaid" value="0"
                       class="w-full rounded-md border border-gray-300 px-3 py-2 text-lg font-medium focus:border-indigo-500 focus:outline-none">
                <div class="mt-1 flex justify-between text-xs text-gray-500">
                    <span>Change</span>
                    <span class="font-medium text-green-700"><span id="posChange">0.00</span> AFN</span>
                </div>
            </div>

            <div>
                <label for="posMethod" class="mb-1 block text-xs font-medium uppercase tracking-wider text-gray-500">
                    Payment Method
                </label>
                <select id="posMethod"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                    <option value="cash">Cash</option>
                    <option value="bank">Bank</option>
                    <option value="credit">Credit</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <button type="button" onclick="submitSale()" id="completeBtn"
                    class="w-full rounded-md bg-green-600 px-4 py-3 text-base font-semibold text-white hover:bg-green-700 disabled:cursor-not-allowed disabled:bg-gray-300">
                Complete Sale (F9)
            </button>
        </div>
    </div>

    {{-- Hidden form --}}
    <form id="saleForm" method="POST" action="{{ route('admin.sales.store') }}" class="hidden">
        @csrf
        <div id="formItems"></div>
        <input type="hidden" name="customer_id" id="formCustomer">
        <input type="hidden" name="discount" id="formDiscount">
        <input type="hidden" name="paid" id="formPaid">
        <input type="hidden" name="payment_method" id="formMethod">
        <input type="hidden" name="status" value="completed">
    </form>

    <script>
        // Data from server
        var PRODUCTS   = {!! json_encode($products) !!};
        var SEARCH_URL = '{{ route('admin.sales.ajax.products') }}';
        var CART       = {};

        // ---------- Product grid ----------
        function renderProductGrid() {
            var grid = document.getElementById('productGrid');
            var empty = document.getElementById('noProducts');

            if (PRODUCTS.length === 0) {
                grid.innerHTML = '';
                empty.classList.remove('hidden');
                return;
            }
            empty.classList.add('hidden');

            var html = '';
            for (var i = 0; i < PRODUCTS.length; i++) {
                var p = PRODUCTS[i];
                var img = p.image
                    ? '<img src="' + p.image + '" class="h-20 w-full rounded object-cover" alt="">'
                    : '<div class="flex h-20 w-full items-center justify-center rounded bg-gray-100 text-xs text-gray-400">No image</div>';

                html += '<button type="button" onclick="addToCart(' + p.id + ')" ' +
                            'class="flex flex-col rounded-lg border border-gray-200 bg-white p-2 text-left transition hover:border-indigo-400 hover:shadow-md">' +
                        img +
                        '<p class="mt-2 line-clamp-2 text-xs font-medium text-gray-800">' + esc(p.name) + '</p>' +
                        '<div class="mt-1 flex items-center justify-between">' +
                            '<span class="text-xs text-gray-500">' + esc(p.sku) + '</span>' +
                            '<span class="text-xs font-semibold text-indigo-600">' + p.selling_price.toFixed(2) + '</span>' +
                        '</div>' +
                        '<p class="mt-0.5 text-[10px] text-gray-400">Stock: ' + p.quantity + '</p>' +
                    '</button>';
            }
            grid.innerHTML = html;
        }

        function esc(str) {
            return String(str).replace(/[&<>"']/g, function (m) {
                return ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' })[m];
            });
        }

        // ---------- Cart ----------
        function addToCart(id) {
            var p = PRODUCTS.find(function (x) { return x.id === id; });
            if (!p) return;

            var item = CART[id];
            if (item) {
                if (item.qty >= p.quantity) {
                    alert('Only ' + p.quantity + ' in stock.');
                    return;
                }
                item.qty++;
            } else {
                CART[id] = { qty: 1, price: p.selling_price, name: p.name, stock: p.quantity };
            }
            renderCart();
        }

        function changeQty(id, delta) {
            var item = CART[id];
            if (!item) return;
            var next = item.qty + delta;
            if (next <= 0) delete CART[id];
            else if (next > item.stock) alert('Only ' + item.stock + ' in stock.');
            else item.qty = next;
            renderCart();
        }

        function setQty(id, v) {
            var item = CART[id];
            if (!item) return;
            var n = parseInt(v) || 0;
            if (n <= 0) delete CART[id];
            else item.qty = Math.min(n, item.stock);
            renderCart();
        }

        function removeFromCart(id) { delete CART[id]; renderCart(); }

        function clearCart() {
            if (Object.keys(CART).length === 0) return;
            if (!confirm('Clear the cart?')) return;
            CART = {};
            renderCart();
        }

        function renderCart() {
            var container = document.getElementById('cartItems');
            var ids = Object.keys(CART);

            if (ids.length === 0) {
                container.innerHTML = '<div class="py-8 text-center text-sm text-gray-400">Cart is empty.<br>Click a product to add it.</div>';
                recalc();
                return;
            }

            var html = '';
            for (var i = 0; i < ids.length; i++) {
                var id = ids[i];
                var item = CART[id];
                var line = (item.qty * item.price).toFixed(2);
                html += '<div class="flex items-center gap-2 rounded-md border border-gray-200 bg-white p-2">' +
                            '<div class="min-w-0 flex-1">' +
                                '<p class="truncate text-sm font-medium text-gray-800">' + esc(item.name) + '</p>' +
                                '<p class="text-xs text-gray-500">' + item.price.toFixed(2) + ' × ' + item.qty + ' = ' + line + '</p>' +
                            '</div>' +
                            '<div class="flex items-center gap-1">' +
                                '<button type="button" onclick="changeQty(' + id + ', -1)" class="h-7 w-7 rounded border border-gray-300 hover:bg-gray-100">&minus;</button>' +
                                '<input type="number" value="' + item.qty + '" min="1" max="' + item.stock + '" onchange="setQty(' + id + ', this.value)" class="h-7 w-12 rounded border border-gray-300 text-center text-sm">' +
                                '<button type="button" onclick="changeQty(' + id + ', 1)" class="h-7 w-7 rounded border border-gray-300 hover:bg-gray-100">+</button>' +
                                '<button type="button" onclick="removeFromCart(' + id + ')" class="ml-1 h-7 w-7 rounded text-red-600 hover:bg-red-50">&times;</button>' +
                            '</div>' +
                        '</div>';
            }
            container.innerHTML = html;
            recalc();
        }

        function recalc() {
            var subtotal = 0;
            Object.keys(CART).forEach(function (id) { subtotal += CART[id].qty * CART[id].price; });

            var discount = parseFloat(document.getElementById('posDiscount').value) || 0;
            var total    = Math.max(0, subtotal - discount);
            var paid     = parseFloat(document.getElementById('posPaid').value) || 0;
            var change   = Math.max(0, paid - total);

            document.getElementById('posSubtotal').textContent = subtotal.toFixed(2);
            document.getElementById('posTotal').textContent    = total.toFixed(2);
            document.getElementById('posChange').textContent   = change.toFixed(2);
            document.getElementById('completeBtn').disabled    = Object.keys(CART).length === 0;
        }

        // ---------- Search ----------
        var searchTimeout = null;
        document.getElementById('searchBox').addEventListener('input', function (e) {
            clearTimeout(searchTimeout);
            var q = e.target.value.trim();
            searchTimeout = setTimeout(function () {
                fetch(SEARCH_URL + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.json(); })
                    .then(function (data) { PRODUCTS = data; renderProductGrid(); })
                    .catch(function () {});
            }, 250);
        });

        // ---------- Submit ----------
        function submitSale() {
            var ids = Object.keys(CART);
            if (ids.length === 0) { alert('Cart is empty.'); return; }

            var formItems = document.getElementById('formItems');
            formItems.innerHTML = '';
            ids.forEach(function (id, idx) {
                var item = CART[id];
                formItems.insertAdjacentHTML('beforeend',
                    '<input type="hidden" name="items[' + idx + '][product_id]" value="' + id + '">' +
                    '<input type="hidden" name="items[' + idx + '][quantity]" value="' + item.qty + '">' +
                    '<input type="hidden" name="items[' + idx + '][price]" value="' + item.price + '">'
                );
            });

            document.getElementById('formCustomer').value = document.getElementById('customer_id').value;
            document.getElementById('formDiscount').value = document.getElementById('posDiscount').value || 0;
            document.getElementById('formPaid').value     = document.getElementById('posPaid').value || 0;
            document.getElementById('formMethod').value   = document.getElementById('posMethod').value;

            document.getElementById('saleForm').submit();
        }

        // ---------- Keyboard shortcuts ----------
        document.addEventListener('keydown', function (e) {
            if (e.key === 'F2') { e.preventDefault(); document.getElementById('searchBox').focus(); }
            if (e.key === 'F4') { e.preventDefault(); document.getElementById('posPaid').focus(); document.getElementById('posPaid').select(); }
            if (e.key === 'F9') {
                e.preventDefault();
                var btn = document.getElementById('completeBtn');
                if (!btn.disabled) btn.click();
            }
        });

        // ---------- Init ----------
        document.addEventListener('DOMContentLoaded', function () {
            renderProductGrid();
            renderCart();
        });
    </script>

@endsection
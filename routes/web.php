<?php

use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user && $user->isStaff()) {
        return redirect()->route('admin.dashboard');
    }

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/', function () {
    if (auth()->check() && auth()->user()->isStaff()) {
        return redirect()->route('admin.dashboard');
    }

    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| ADMIN AREA
|--------------------------------------------------------------------------
| Two groups:
|   1. Staff-accessible (admin + staff)
|   2. Admin-only (admin only)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,staff'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])
    ->name('notifications.index');

        // POS — must be before sales resource
        Route::get('/sales/pos', [SaleController::class, 'pos'])->name('sales.pos');
        Route::get('/sales/ajax/products', [SaleController::class, 'ajaxProducts'])->name('sales.ajax.products');

        // Sales
        Route::resource('sales', SaleController::class)->except(['edit', 'update']);
        Route::post('/sales/{sale}/payments', [PaymentController::class, 'storeForSale'])
            ->name('sales.payments.store');

        // Customers
        Route::resource('customers', CustomerController::class);

        // Products — view only for staff (see, list, show)
        Route::resource('products', ProductController::class)->only(['index', 'show']);

        // Stock — view only for staff
        Route::prefix('stock')->name('stock.')->group(function () {
            Route::get('/', [StockController::class, 'index'])->name('index');
            Route::get('/movements', [StockController::class, 'movements'])->name('movements');
           
        });
    });

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Categories
        Route::resource('categories', CategoryController::class);

        // Brands
        Route::resource('brands', BrandController::class);

        // Suppliers
        Route::resource('suppliers', SupplierController::class);

        // Products — full CRUD for admin
        Route::resource('products', ProductController::class)
            ->except(['index', 'show']); // index/show already in staff group

        // Purchases
        Route::resource('purchases', PurchaseController::class);
        Route::post('/purchases/{purchase}/payments', [PaymentController::class, 'storeForPurchase'])
            ->name('purchases.payments.store');

        // Stock adjustments (admin only)
        Route::prefix('stock')->name('stock.')->group(function () {
            Route::get('/adjust/{product}', [StockController::class, 'adjustForm'])->name('adjust.form');
            Route::post('/adjust/{product}', [StockController::class, 'adjust'])->name('adjust');
        });

        // Expenses
        Route::resource('expenses', ExpenseController::class);

        // Payments — global list + delete
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/',         [ReportController::class, 'index'])->name('index');
            Route::get('/sales',    [ReportController::class, 'sales'])->name('sales');
            Route::get('/purchases',[ReportController::class, 'purchases'])->name('purchases');
            Route::get('/stock',    [ReportController::class, 'stock'])->name('stock');
            Route::get('/profit',   [ReportController::class, 'profit'])->name('profit');
        });

        // Users
        Route::resource('users', UserController::class);
    });

require __DIR__.'/auth.php';
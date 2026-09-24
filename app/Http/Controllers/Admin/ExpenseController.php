<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $search     = $request->input('search');
        $category   = $request->input('category');
        $dateFrom   = $request->input('date_from');
        $dateTo     = $request->input('date_to');

        $expenses = Expense::query()
            ->with('user')
            ->when($search, fn ($q) => $q->where('title', 'like', "%{$search}%")
                                        ->orWhere('description', 'like', "%{$search}%"))
            ->when($category, fn ($q) => $q->where('category', $category))
            ->when($dateFrom, fn ($q) => $q->whereDate('date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('date', '<=', $dateTo))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        // Summary
        $totalAllTime   = Expense::sum('amount');
        $totalThisMonth = Expense::whereMonth('date', now()->month)
                                 ->whereYear('date', now()->year)
                                 ->sum('amount');
        $countThisMonth = Expense::whereMonth('date', now()->month)
                                 ->whereYear('date', now()->year)
                                 ->count();

        $categories = Expense::categories();

        return view('admin.expenses.index', compact(
            'expenses', 'search', 'category', 'dateFrom', 'dateTo',
            'totalAllTime', 'totalThisMonth', 'countThisMonth', 'categories'
        ));
    }

    public function create()
    {
        $categories = Expense::categories();
        return view('admin.expenses.create', compact('categories'));
    }

    public function store(StoreExpenseRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();

        Expense::create($data);

        return redirect()
            ->route('admin.expenses.index')
            ->with('success', 'Expense recorded successfully.');
    }

    public function show(Expense $expense)
    {
        return redirect()->route('admin.expenses.index');
    }

    public function edit(Expense $expense)
    {
        $categories = Expense::categories();
        return view('admin.expenses.edit', compact('expense', 'categories'));
    }

    public function update(UpdateExpenseRequest $request, Expense $expense)
    {
        $expense->update($request->validated());

        return redirect()
            ->route('admin.expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()
            ->route('admin.expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{

    public function index(Request $request)
    {
        $search = $request->input('search');
        $categoryFilter = $request->input('category_id');
        $stockFilter = $request->input('stock'); // low | out

        $products = Product::query()
            ->with(['category', 'brand', 'supplier'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhere('model', 'like', "%{$search}%");
                });
            })
            ->when($categoryFilter, fn ($q) => $q->where('category_id', $categoryFilter))
            ->when($stockFilter === 'low', fn ($q) => $q->whereColumn('quantity', '<=', 'minimum_stock')->where('quantity', '>', 0))
            ->when($stockFilter === 'out', fn ($q) => $q->where('quantity', '<=', 0))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'search', 'categories', 'categoryFilter', 'stockFilter'));
    }

    public function create()
    {
        $categories = Category::where('status', 'active')->orderBy('name')->get();
        $brands     = Brand::where('status', 'active')->orderBy('name')->get();
        $suppliers  = Supplier::where('status', 'active')->orderBy('name')->get();

        return view('admin.products.create', compact('categories', 'brands', 'suppliers'));
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'brand', 'supplier']);
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::where('status', 'active')->orderBy('name')->get();
        $brands     = Brand::where('status', 'active')->orderBy('name')->get();
        $suppliers  = Supplier::where('status', 'active')->orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories', 'brands', 'suppliers'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        // Prevent deletion if product is referenced by sales/purchases
        $hasReferences = $product->stockMovements()->exists();

        if ($hasReferences) {
            return redirect()
                ->route('admin.products.index')
                ->with('error', 'Cannot delete this product: it has stock history. Consider marking it inactive instead.');
        }

        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
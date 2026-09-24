<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::where('name', 'admin')->first();
        $this->admin = User::create([
            'name'     => 'Admin',
            'email'    => 'admin@test.com',
            'password' => bcrypt('password'),
            'role_id'  => $role->id,
            'status'   => 'active',
        ]);

        $category = Category::create(['name' => 'Battery', 'status' => 'active']);
        $brand    = Brand::create(['name' => 'Apple', 'status' => 'active']);

        $this->product = Product::create([
            'category_id'    => $category->id,
            'brand_id'       => $brand->id,
            'name'           => 'iPhone Battery',
            'sku'            => 'BAT-001',
            'purchase_price' => 500,
            'selling_price'  => 800,
            'quantity'       => 10,
            'minimum_stock'  => 2,
            'status'         => 'active',
        ]);
    }

    public function test_sale_creation_decreases_stock_and_creates_movement(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/sales', [
            'customer_id'    => null,
            'discount'       => 0,
            'paid'           => 1600,
            'payment_method' => 'cash',
            'status'         => 'completed',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity'   => 2,
                    'price'      => 800,
                ],
            ],
        ]);

        $response->assertRedirect();

        // Product stock decreased from 10 to 8
        $this->product->refresh();
        $this->assertEquals(8, $this->product->quantity);

        // Sale record created
        $this->assertDatabaseCount('sales', 1);

        // Stock movement recorded with negative quantity
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'type'       => 'sale',
            'quantity'   => -2,
        ]);
    }

    public function test_sale_cannot_exceed_available_stock(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/sales', [
            'customer_id'    => null,
            'discount'       => 0,
            'paid'           => 0,
            'payment_method' => 'credit',
            'status'         => 'completed',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity'   => 100, // way more than 10
                    'price'      => 800,
                ],
            ],
        ]);

        // No sale created, stock unchanged
        $this->assertDatabaseCount('sales', 0);
        $this->product->refresh();
        $this->assertEquals(10, $this->product->quantity);
    }

    public function test_sale_creates_invoice_number(): void
    {
        $this->actingAs($this->admin)->post('/admin/sales', [
            'customer_id'    => null,
            'discount'       => 0,
            'paid'           => 800,
            'payment_method' => 'cash',
            'status'         => 'completed',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity'   => 1,
                    'price'      => 800,
                ],
            ],
        ]);

        $sale = Sale::first();
        $this->assertNotNull($sale);
        $this->assertStringStartsWith('INV-', $sale->invoice_no);
    }
}
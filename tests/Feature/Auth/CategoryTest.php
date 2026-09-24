<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

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
    }

    public function test_admin_can_view_categories_index(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/categories');
        $response->assertStatus(200);
    }

    public function test_admin_can_create_category(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/categories', [
            'name'        => 'Battery',
            'description' => 'Phone batteries',
            'status'      => 'active',
        ]);

        $response->assertRedirect('/admin/categories');
        $this->assertDatabaseHas('categories', ['name' => 'Battery']);
    }

    public function test_category_name_is_required(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/categories', [
            'name'   => '',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('categories', 0);
    }

    public function test_category_name_must_be_unique(): void
    {
        Category::create(['name' => 'Screen', 'status' => 'active']);

        $response = $this->actingAs($this->admin)->post('/admin/categories', [
            'name'   => 'Screen',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('categories', 1);
    }

    public function test_admin_can_update_category(): void
    {
        $category = Category::create(['name' => 'Old Name', 'status' => 'active']);

        $response = $this->actingAs($this->admin)->put("/admin/categories/{$category->id}", [
            'name'        => 'New Name',
            'description' => 'Updated',
            'status'      => 'active',
        ]);

        $response->assertRedirect('/admin/categories');
        $this->assertDatabaseHas('categories', [
            'id'   => $category->id,
            'name' => 'New Name',
        ]);
    }

    public function test_admin_can_delete_category_without_products(): void
    {
        $category = Category::create(['name' => 'Temp', 'status' => 'active']);

        $response = $this->actingAs($this->admin)->delete("/admin/categories/{$category->id}");

        $response->assertRedirect('/admin/categories');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
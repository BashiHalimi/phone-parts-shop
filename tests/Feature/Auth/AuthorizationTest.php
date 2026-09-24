<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $roleName): User
    {
        $role = Role::where('name', $roleName)->first();

        return User::create([
            'name'     => ucfirst($roleName),
            'email'    => $roleName . '@test.com',
            'password' => bcrypt('password'),
            'role_id'  => $role->id,
            'status'   => 'active',
        ]);
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = $this->makeUser('admin');

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
    }

    public function test_staff_can_access_admin_dashboard(): void
    {
        $staff = $this->makeUser('staff');

        $response = $this->actingAs($staff)->get('/admin');
        $response->assertStatus(200);
    }

    public function test_staff_cannot_access_users_page(): void
    {
        $staff = $this->makeUser('staff');

        $response = $this->actingAs($staff)->get('/admin/users');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_users_page(): void
    {
        $admin = $this->makeUser('admin');

        $response = $this->actingAs($admin)->get('/admin/users');
        $response->assertStatus(200);
    }

    public function test_staff_cannot_access_suppliers_page(): void
    {
        $staff = $this->makeUser('staff');

        $response = $this->actingAs($staff)->get('/admin/suppliers');
        $response->assertStatus(403);
    }

    public function test_staff_can_access_pos(): void
    {
        $staff = $this->makeUser('staff');

        $response = $this->actingAs($staff)->get('/admin/sales/pos');
        $response->assertStatus(200);
    }

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/login');
    }
}
<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_loads(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_user_can_log_in_with_correct_credentials(): void
    {
        $role = Role::where('name', 'admin')->first();

        $user = User::create([
            'name'     => 'Admin',
            'email'    => 'admin@test.com',
            'password' => bcrypt('password'),
            'role_id'  => $role->id,
            'status'   => 'active',
        ]);

        $response = $this->post('/login', [
            'email'    => 'admin@test.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/admin');
    }

    public function test_user_cannot_log_in_with_wrong_password(): void
    {
        $role = Role::where('name', 'admin')->first();

        User::create([
            'name'     => 'Admin',
            'email'    => 'admin@test.com',
            'password' => bcrypt('password'),
            'role_id'  => $role->id,
            'status'   => 'active',
        ]);

        $this->post('/login', [
            'email'    => 'admin@test.com',
            'password' => 'wrong',
        ]);

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_log_in(): void
{
    $role = Role::where('name', 'staff')->first();

    User::create([
        'name'     => 'Staff',
        'email'    => 'staff@test.com',
        'password' => bcrypt('password'),
        'role_id'  => $role->id,
        'status'   => 'inactive',
    ]);

    $response = $this->post('/login', [
        'email'    => 'staff@test.com',
        'password' => 'password',
    ]);

    // User should NOT be authenticated
    $this->assertGuest();

    // Should be redirected back to login with an error
    $response->assertSessionHasErrors('email');
}
}
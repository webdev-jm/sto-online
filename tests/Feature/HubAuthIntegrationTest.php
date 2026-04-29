<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HubAuthIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    // ---------------------------------------------------------------------------
    // Redirect
    // ---------------------------------------------------------------------------

    public function test_get_login_hub_redirects_to_hub_oauth_authorize(): void
    {
        $response = $this->get('/login/hub');

        $response->assertRedirectContains('oauth/authorize');
    }

    // ---------------------------------------------------------------------------
    // Callback — new user
    // ---------------------------------------------------------------------------

    public function test_callback_creates_new_user_and_logs_in(): void
    {
        $socialiteUser = $this->makeSocialiteUser(id: 42, name: 'Test User', email: 'test@example.com');
        Socialite::shouldReceive('driver->user')->once()->andReturn($socialiteUser);

        $response = $this->get('/login/hub/callback');

        $response->assertRedirect('/home');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['hub_user_id' => '42']);
    }

    // ---------------------------------------------------------------------------
    // Callback — returning user
    // ---------------------------------------------------------------------------

    public function test_callback_finds_existing_user_without_creating_duplicate(): void
    {
        User::create([
            'hub_user_id' => '42',
            'name'        => 'Test User',
            'email'       => 'test@example.com',
            'username'    => 'testuser',
            'password'    => null,
        ]);

        $socialiteUser = $this->makeSocialiteUser(id: 42, name: 'Test User', email: 'test@example.com');
        Socialite::shouldReceive('driver->user')->once()->andReturn($socialiteUser);

        $this->get('/login/hub/callback');

        $this->assertSame(1, \App\Models\User::where('hub_user_id', '42')->count());
    }

    // ---------------------------------------------------------------------------
    // /admin/hub — role gate
    // ---------------------------------------------------------------------------

    public function test_admin_hub_returns_403_without_admin_role(): void
    {
        Role::firstOrCreate(['name' => 'default_user', 'guard_name' => 'web']);

        $user = User::create([
            'hub_user_id' => '1',
            'name'        => 'Regular User',
            'email'       => 'regular@example.com',
            'username'    => 'regularuser',
            'password'    => null,
        ]);
        $user->assignRole('default_user');

        $response = $this->actingAs($user)->get('/admin/hub');

        $response->assertStatus(403);
    }

    public function test_admin_hub_returns_200_with_admin_role(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $user = User::create([
            'hub_user_id' => '2',
            'name'        => 'Admin User',
            'email'       => 'admin@example.com',
            'username'    => 'adminuser',
            'password'    => null,
        ]);
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get('/admin/hub');

        $response->assertStatus(200);
    }

    // ---------------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------------

    protected function makeSocialiteUser(int $id, string $name, string $email): SocialiteUser
    {
        $user = new SocialiteUser();
        $user->map(['id' => $id, 'name' => $name, 'email' => $email]);

        return $user;
    }
}

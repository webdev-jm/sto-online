<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{

    use RefreshDatabase;

    /** @test */
    public function it_creates_a_user_successfully() {

        // Arrange: Create a user using the factory
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john.doe@gmail.com',
            'username' => 'john.doe',
            'password' => Hash::make('john.doe123!'),
        ]);

        // Act: Retrieve the user from the database
        $retrievedUser = User::find($user->id);

        // Assert: Check if the user data matches
        $this->assertNotNull($retrievedUser);
        $this->assertEquals('John Doe', $retrievedUser->name);
        $this->assertEquals('john.doe@gmail.com', $retrievedUser->email);
    }

    /** @test */
    public function it_validates_user_email_uniqueness() {
        // Arrange: Create a user
        User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Act: Attempt to create another user with the same email
        $this->expectException(\Illuminate\Database\QueryException::class);

        User::factory()->create([
            'email' => 'test@example.com',
        ]);
    }

    /** @test */
    public function it_rejects_invalid_authentication_attempts()
    {
        // Arrange: Create a user
        $user = User::factory()->create([
            'username' => 'john.doe',
            'password' => Hash::make('test123!'),
        ]);

        // Act: Attempt to authenticate with wrong credentials
        $credentials = ['email' => 'test@example.com', 'password' => 'wrongpassword'];
        $this->assertFalse(auth()->attempt($credentials));
    }

    /** @test */
    public function it_creates_a_user_and_redirects()
    {
        $response = $this->post(route('user.store'), [
            'account_id' => 1,
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'username' => 'johndoe',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'type' => 1,
            'role_ids' => [1],
        ]);

        $response->assertRedirect(route('user.index'));
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
        ]);
    }
}

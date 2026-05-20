<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class UnifiedUploadPageTest extends TestCase
{
    use DatabaseTransactions;

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/uploads');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_without_branch_session_is_redirected(): void
    {
        $user = User::factory()->create([
            'type'       => 1,
            'username'   => fake()->unique()->userName(),
            'account_id' => null,
        ]);

        $response = $this->actingAs($user)->get('/uploads');

        // checkBranch() redirects when no branch in session
        $response->assertRedirect();
    }

    public function test_authenticated_user_with_branch_session_sees_200(): void
    {
        $user = User::factory()->create([
            'type'       => 1,
            'username'   => fake()->unique()->userName(),
            'account_id' => null,
        ]);

        $response = $this->actingAs($user)
            ->withSession([
                'account' => (object) [
                    'id'           => 1,
                    'short_name'   => 'Test Account',
                    'account_code' => 'TEST',
                ],
                'account_branch' => (object) [
                    'id'   => 1,
                    'code' => 'BR01',
                    'name' => 'Branch 1',
                ],
            ])
            ->get('/uploads');

        $response->assertStatus(200);
    }

    public function test_page_contains_uploads_tabs(): void
    {
        $user = User::factory()->create([
            'type'       => 1,
            'username'   => fake()->unique()->userName(),
            'account_id' => null,
        ]);

        $response = $this->actingAs($user)
            ->withSession([
                'account' => (object) [
                    'id'           => 1,
                    'short_name'   => 'Test Account',
                    'account_code' => 'TEST',
                ],
                'account_branch' => (object) [
                    'id'   => 1,
                    'code' => 'BR01',
                    'name' => 'Branch 1',
                ],
            ])
            ->get('/uploads');

        $response->assertStatus(200);
        $response->assertSee('uploads-tabs');
    }
}

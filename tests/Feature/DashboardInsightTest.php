<?php

namespace Tests\Feature;

use App\Exceptions\AiUnavailableException;
use App\Models\User;
use App\Services\OllamaService;
use App\Services\RagService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class DashboardInsightTest extends TestCase
{
    use DatabaseTransactions;

    private function ollamaResponse(string $content): array
    {
        return [
            'message' => ['role' => 'assistant', 'content' => $content],
        ];
    }

    private function actingAsAdmin(): User
    {
        $user = User::factory()->create(['type' => 1]);
        $this->actingAs($user);
        return $user;
    }

    // ---------------------------------------------------------------------------
    // Rendering
    // ---------------------------------------------------------------------------

    public function test_sales_insight_component_renders(): void
    {
        $this->actingAsAdmin();

        Livewire::test('dashboard.insight', ['year' => 2025, 'type' => 'sales'])
            ->assertStatus(200)
            ->assertSet('type', 'sales')
            ->assertSet('hasGenerated', false)
            ->assertSet('insight', '');
    }

    public function test_inventories_insight_component_renders(): void
    {
        $this->actingAsAdmin();

        Livewire::test('dashboard.insight', ['year' => 2025, 'type' => 'inventories'])
            ->assertStatus(200)
            ->assertSet('type', 'inventories')
            ->assertSet('hasGenerated', false);
    }

    // ---------------------------------------------------------------------------
    // Generate Insight
    // ---------------------------------------------------------------------------

    public function test_generate_insight_calls_ollama_and_stores_result(): void
    {
        Http::fake(['*/api/chat' => Http::response($this->ollamaResponse('Sales are trending up in Q3.'))]);

        $this->actingAsAdmin();

        Livewire::test('dashboard.insight', ['year' => 2025, 'type' => 'sales'])
            ->call('generateInsight')
            ->assertSet('hasGenerated', true)
            ->assertSet('isGenerating', false)
            ->assertSet('scope', 'Overall')
            ->assertSet('insight', 'Sales are trending up in Q3.');
    }

    public function test_generate_insight_for_inventories_calls_ollama(): void
    {
        Http::fake(['*/api/chat' => Http::response($this->ollamaResponse('Inventory expiry risk is high.'))]);

        $this->actingAsAdmin();

        Livewire::test('dashboard.insight', ['year' => 2025, 'type' => 'inventories'])
            ->call('generateInsight')
            ->assertSet('hasGenerated', true)
            ->assertSet('insight', 'Inventory expiry risk is high.');
    }

    // ---------------------------------------------------------------------------
    // Year Change Resets Insight
    // ---------------------------------------------------------------------------

    public function test_year_change_resets_insight(): void
    {
        Http::fake(['*/api/chat' => Http::response($this->ollamaResponse('Good year overall.'))]);

        $this->actingAsAdmin();

        // $year is a #[Reactive] prop; test resetInsight() which updatedYear() delegates to
        Livewire::test('dashboard.insight', ['year' => 2025, 'type' => 'sales'])
            ->call('generateInsight')
            ->assertSet('hasGenerated', true)
            ->assertSet('insight', 'Good year overall.')
            ->call('resetInsight')
            ->assertSet('hasGenerated', false)
            ->assertSet('insight', '');
    }

    // ---------------------------------------------------------------------------
    // Insight uses mocked OllamaService
    // ---------------------------------------------------------------------------

    public function test_generate_insight_uses_ollama_service(): void
    {
        $this->actingAsAdmin();

        $mock = Mockery::mock(OllamaService::class);
        $mock->shouldReceive('chat')
            ->once()
            ->andReturn('Mocked AI insight for sales.');

        $this->app->instance(OllamaService::class, $mock);

        Livewire::test('dashboard.insight', ['year' => 2025, 'type' => 'sales'])
            ->call('generateInsight')
            ->assertSet('insight', 'Mocked AI insight for sales.')
            ->assertSet('hasGenerated', true);
    }

    // ---------------------------------------------------------------------------
    // RAG path — account in session
    // ---------------------------------------------------------------------------

    public function test_generate_sales_insight_uses_rag_when_account_in_session(): void
    {
        Http::fake(['*/api/chat' => Http::response($this->ollamaResponse('RAG-powered sales insight.'))]);

        $this->actingAsAdmin();

        $ragMock = Mockery::mock(RagService::class);
        $ragMock->shouldReceive('retrieve')
            ->times(3) // three queries fired by retrieveRagChunks
            ->andReturn(['Sales 2025-03: Kojie.San Soap, Qty: 144 CS, PHP 3,833.57.']);

        $this->app->instance(RagService::class, $ragMock);

        $account               = new \stdClass();
        $account->account_code = '3000075';
        $account->short_name   = 'TEST ACCOUNT';
        session(['account' => $account]);

        Livewire::test('dashboard.insight', ['year' => 2025, 'type' => 'sales'])
            ->call('generateInsight')
            ->assertSet('hasGenerated', true)
            ->assertSet('insight', 'RAG-powered sales insight.');
    }

    // ---------------------------------------------------------------------------
    // AI Unavailable Error Handling
    // ---------------------------------------------------------------------------

    public function test_generate_insight_shows_error_when_ollama_is_unreachable(): void
    {
        $this->actingAsAdmin();

        $mock = Mockery::mock(OllamaService::class);
        $mock->shouldReceive('chat')
            ->once()
            ->andThrow(new AiUnavailableException('AI service is unreachable.'));

        $this->app->instance(OllamaService::class, $mock);

        Livewire::test('dashboard.insight', ['year' => 2025, 'type' => 'sales'])
            ->call('generateInsight')
            ->assertSet('hasGenerated', false)
            ->assertSet('isGenerating', false)
            ->assertSet('insight', '')
            ->assertSet('error', 'Unable to reach the AI service. Please ensure Ollama is running and try again.');
    }

    public function test_generate_insight_shows_error_when_ollama_returns_http_error(): void
    {
        Http::fake(['*/api/chat' => Http::response('Internal Server Error', 500)]);

        $this->actingAsAdmin();

        Livewire::test('dashboard.insight', ['year' => 2025, 'type' => 'sales'])
            ->call('generateInsight')
            ->assertSet('hasGenerated', false)
            ->assertSet('isGenerating', false)
            ->assertSet('insight', '')
            ->assertSet('error', 'Unable to reach the AI service. Please ensure Ollama is running and try again.');
    }

    public function test_generate_insight_clears_error_on_successful_retry(): void
    {
        $this->actingAsAdmin();

        // First call fails, sets error
        $failMock = Mockery::mock(OllamaService::class);
        $failMock->shouldReceive('chat')
            ->once()
            ->andThrow(new AiUnavailableException('AI service is unreachable.'));

        $this->app->instance(OllamaService::class, $failMock);

        $component = Livewire::test('dashboard.insight', ['year' => 2025, 'type' => 'sales'])
            ->call('generateInsight')
            ->assertSet('error', 'Unable to reach the AI service. Please ensure Ollama is running and try again.');

        // Second call succeeds — swap mock and retry
        $successMock = Mockery::mock(OllamaService::class);
        $successMock->shouldReceive('chat')
            ->once()
            ->andReturn('Recovery insight.');

        $this->app->instance(OllamaService::class, $successMock);

        $component
            ->call('generateInsight')
            ->assertSet('error', '')
            ->assertSet('hasGenerated', true)
            ->assertSet('insight', 'Recovery insight.');
    }

    public function test_generate_inventory_insight_uses_rag_when_account_in_session(): void
    {
        Http::fake(['*/api/chat' => Http::response($this->ollamaResponse('RAG-powered inventory insight.'))]);

        $this->actingAsAdmin();

        $ragMock = Mockery::mock(RagService::class);
        $ragMock->shouldReceive('retrieve')
            ->times(3) // three queries fired by retrieveRagChunks
            ->andReturn(['Inventory: Location A, Kojie.San Soap, 200 units, expiry 2025-06-01.']);

        $this->app->instance(RagService::class, $ragMock);

        $account               = new \stdClass();
        $account->account_code = '3000075';
        $account->short_name   = 'TEST ACCOUNT';
        session(['account' => $account]);

        Livewire::test('dashboard.insight', ['year' => 2025, 'type' => 'inventories'])
            ->call('generateInsight')
            ->assertSet('hasGenerated', true)
            ->assertSet('insight', 'RAG-powered inventory insight.');
    }
}

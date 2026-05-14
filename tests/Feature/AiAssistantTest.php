<?php

namespace Tests\Feature;

use App\Exceptions\AiUnavailableException;
use App\Http\Livewire\AiAssistant;
use App\Models\Account;
use App\Models\User;
use App\Services\OllamaService;
use App\Services\RagService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class AiAssistantTest extends TestCase
{
    use DatabaseTransactions;

    private function ollamaResponse(string $content): array
    {
        return [
            'message' => ['role' => 'assistant', 'content' => $content],
        ];
    }

    private function actingAsUser(): User
    {
        $user = User::factory()->create(['type' => 0]);
        $this->actingAs($user);
        return $user;
    }

    // ---------------------------------------------------------------------------
    // Toggle
    // ---------------------------------------------------------------------------

    public function test_toggle_opens_and_closes_widget(): void
    {
        Http::fake(['*/api/chat' => Http::response($this->ollamaResponse('Hello!'))]);

        $this->actingAsUser();

        Livewire::test(AiAssistant::class)
            ->assertSet('isOpen', false)
            ->call('toggle')
            ->assertSet('isOpen', true)
            ->call('toggle')
            ->assertSet('isOpen', false);
    }

    // ---------------------------------------------------------------------------
    // Generate Insights
    // ---------------------------------------------------------------------------

    public function test_generate_insights_appends_assistant_message(): void
    {
        Http::fake(['*/api/chat' => Http::response($this->ollamaResponse('I am your STO assistant!'))]);

        $this->actingAsUser();

        Livewire::test(AiAssistant::class)
            ->call('generateInsights')
            ->assertSet('insightsGenerated', true)
            ->assertSet('isLoading', false)
            ->assertCount('messages', 1)
            ->assertSet('messages.0.role', 'assistant')
            ->assertSet('messages.0.content', 'I am your STO assistant!');
    }

    public function test_toggle_generates_insights_only_once(): void
    {
        Http::fake(['*/api/chat' => Http::response($this->ollamaResponse('Hi there!'))]);

        $this->actingAsUser();

        $component = Livewire::test(AiAssistant::class)
            ->call('toggle')
            ->assertCount('messages', 1);

        Http::fake(['*/api/chat' => Http::response($this->ollamaResponse('Second call'))]);

        $component
            ->call('toggle')  // close
            ->call('toggle')  // reopen — should NOT call Ollama again
            ->assertCount('messages', 1);
    }

    // ---------------------------------------------------------------------------
    // Send Message (no account in session — no RAG)
    // ---------------------------------------------------------------------------

    public function test_send_message_appends_user_and_assistant_messages(): void
    {
        Http::fake(['*/api/chat' => Http::response($this->ollamaResponse('Here is the answer.'))]);

        $this->actingAsUser();

        Livewire::test(AiAssistant::class)
            ->set('userInput', 'What is my sales performance?')
            ->call('sendMessage')
            ->assertSet('userInput', '')
            ->assertSet('isLoading', false)
            ->assertCount('messages', 2)
            ->assertSet('messages.0.role', 'user')
            ->assertSet('messages.0.content', 'What is my sales performance?')
            ->assertSet('messages.1.role', 'assistant')
            ->assertSet('messages.1.content', 'Here is the answer.');
    }

    public function test_send_message_with_empty_input_does_nothing(): void
    {
        Http::fake();

        $this->actingAsUser();

        Livewire::test(AiAssistant::class)
            ->set('userInput', '   ')
            ->call('sendMessage')
            ->assertCount('messages', 0);

        Http::assertNothingSent();
    }

    // ---------------------------------------------------------------------------
    // Send Message with RAG context (account in session)
    // ---------------------------------------------------------------------------

    public function test_send_message_injects_rag_chunks_when_account_in_session(): void
    {
        Http::fake(['*/api/chat' => Http::response($this->ollamaResponse('Top product is Kojie.San Soap.'))]);

        $this->actingAsUser();

        $ragMock = Mockery::mock(RagService::class);
        $ragMock->shouldReceive('retrieve')
            ->once()
            ->with('What is my top product?', '3000075')
            ->andReturn(['Sales 2025-07: Kojie.San Classic Soap, Qty: 144 CS, PHP 3,833.57.']);

        $this->app->instance(RagService::class, $ragMock);

        $account               = new \stdClass();
        $account->account_code = '3000075';
        $account->short_name   = 'TEST ACCOUNT';
        session(['account' => $account]);

        Livewire::test(AiAssistant::class)
            ->set('userInput', 'What is my top product?')
            ->call('sendMessage')
            ->assertSet('messages.1.content', 'Top product is Kojie.San Soap.');
    }

    // ---------------------------------------------------------------------------
    // AI Unavailable Error Handling
    // ---------------------------------------------------------------------------

    public function test_generate_insights_appends_error_message_when_ollama_is_unreachable(): void
    {
        $this->actingAsUser();

        $mock = Mockery::mock(OllamaService::class);
        $mock->shouldReceive('chat')
            ->once()
            ->andThrow(new AiUnavailableException('AI service is unreachable.'));

        $this->app->instance(OllamaService::class, $mock);

        Livewire::test(AiAssistant::class)
            ->call('generateInsights')
            ->assertSet('insightsGenerated', false)
            ->assertSet('isLoading', false)
            ->assertCount('messages', 1)
            ->assertSet('messages.0.role', 'assistant')
            ->assertSet('messages.0.content', 'I\'m unable to connect to the AI service right now. Please ensure Ollama is running and try again.');
    }

    public function test_send_message_appends_error_message_when_ollama_is_unreachable(): void
    {
        $this->actingAsUser();

        $mock = Mockery::mock(OllamaService::class);
        $mock->shouldReceive('chat')
            ->once()
            ->andThrow(new AiUnavailableException('AI service is unreachable.'));

        $this->app->instance(OllamaService::class, $mock);

        Livewire::test(AiAssistant::class)
            ->set('userInput', 'Show me my top products')
            ->call('sendMessage')
            ->assertSet('isLoading', false)
            ->assertCount('messages', 2)
            ->assertSet('messages.0.role', 'user')
            ->assertSet('messages.1.role', 'assistant')
            ->assertSet('messages.1.content', 'I\'m unable to connect to the AI service right now. Please ensure Ollama is running and try again.');
    }

    public function test_generate_insights_appends_error_message_when_ollama_returns_http_error(): void
    {
        Http::fake(['*/api/chat' => Http::response('Internal Server Error', 500)]);

        $this->actingAsUser();

        Livewire::test(AiAssistant::class)
            ->call('generateInsights')
            ->assertSet('insightsGenerated', false)
            ->assertSet('isLoading', false)
            ->assertCount('messages', 1)
            ->assertSet('messages.0.role', 'assistant')
            ->assertSet('messages.0.content', 'I\'m unable to connect to the AI service right now. Please ensure Ollama is running and try again.');
    }

    // ---------------------------------------------------------------------------
    // Rendering
    // ---------------------------------------------------------------------------

    public function test_component_renders_for_authenticated_user(): void
    {
        Http::fake();

        $this->actingAsUser();

        Livewire::test(AiAssistant::class)
            ->assertStatus(200);
    }
}

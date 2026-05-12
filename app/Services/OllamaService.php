<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OllamaService
{
    /**
     * Send a chat request to the Ollama API.
     *
     * @param array<int, array{role: string, content: string}> $messages
     */
    public function chat(array $messages): string
    {
        $response = Http::timeout(60)->post(config('services.ollama.url') . '/api/chat', [
            'model'    => config('services.ollama.model'),
            'messages' => $messages,
            'stream'   => false,
        ]);

        return $response->json('message.content', '');
    }
}

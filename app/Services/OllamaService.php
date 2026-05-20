<?php

namespace App\Services;

use App\Exceptions\AiUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class OllamaService
{
    /**
     * Send a chat request to the Ollama API.
     *
     * @param array<int, array{role: string, content: string}> $messages
     * @throws AiUnavailableException
     */
    public function chat(array $messages): string
    {
        try {
            $response = Http::timeout(120)->post(config('services.ollama.url') . '/api/chat', [
                'model'    => config('services.ollama.model'),
                'messages' => $messages,
                'stream'   => false,
            ]);
        } catch (ConnectionException $e) {
            throw new AiUnavailableException('AI service is unreachable.', 0, $e);
        }

        if ($response->failed()) {
            throw new AiUnavailableException('AI service returned an error.');
        }

        $content = trim($response->json('message.content', ''));

        if ($content === '') {
            throw new AiUnavailableException('AI service returned an empty response.');
        }

        return $content;
    }
}

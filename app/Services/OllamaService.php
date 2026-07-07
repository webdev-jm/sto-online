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
            $response = Http::timeout(300)->post(config('services.ollama.url') . '/api/chat', [
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

    /**
     * Generate an embedding vector for the given text using Ollama's /api/embed endpoint.
     *
     * @return float[]
     */
    public function embed(string $text): array
    {
        $response = Http::timeout(60)->post(config('services.ollama.url') . '/api/embed', [
            'model' => config('services.ollama.embed_model', config('services.ollama.model')),
            'input' => $text,
        ]);

        $embeddings = $response->json('embeddings');

        return $embeddings[0] ?? [];
    }

    /**
     * Generate embeddings for multiple texts in a single Ollama request.
     *
     * @param  string[]  $texts
     * @return float[][]
     */
    public function embedBatch(array $texts): array
    {
        if (empty($texts)) {
            return [];
        }

        $response = Http::timeout(120)->post(config('services.ollama.url') . '/api/embed', [
            'model' => config('services.ollama.embed_model', config('services.ollama.model')),
            'input' => $texts,
        ]);

        return $response->json('embeddings') ?? [];
    }
}

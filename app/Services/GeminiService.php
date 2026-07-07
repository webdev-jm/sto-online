<?php

namespace App\Services;

use App\Exceptions\AiUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class GeminiService
{
    private const API_BASE = 'https://generativelanguage.googleapis.com/v1beta/models';

    /**
     * Send a chat request to the Gemini API.
     *
     * Mirrors OllamaService::chat()'s signature/behavior so it can be swapped in as a drop-in
     * replacement: same $messages shape (role: system|user|assistant, content: string) and the
     * same AiUnavailableException on failure.
     *
     * @param array<int, array{role: string, content: string}> $messages
     * @throws AiUnavailableException
     */
    public function chat(array $messages): string
    {
        $systemInstruction = implode("\n\n", array_column(
            array_filter($messages, fn (array $m): bool => $m['role'] === 'system'),
            'content'
        ));

        $contents = array_values(array_map(
            fn (array $m): array => [
                'role'  => $m['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $m['content']]],
            ],
            array_filter($messages, fn (array $m): bool => $m['role'] !== 'system')
        ));

        $payload = ['contents' => $contents];

        if ($systemInstruction !== '') {
            $payload['systemInstruction'] = ['parts' => [['text' => $systemInstruction]]];
        }

        try {
            $response = Http::timeout(300)
                ->withHeaders(['x-goog-api-key' => config('services.gemini.key')])
                ->post(self::API_BASE . '/' . config('services.gemini.model') . ':generateContent', $payload);
        } catch (ConnectionException $e) {
            throw new AiUnavailableException('AI service is unreachable.', 0, $e);
        }

        if ($response->failed()) {
            throw new AiUnavailableException('AI service returned an error.');
        }

        $content = trim($response->json('candidates.0.content.parts.0.text', ''));

        if ($content === '') {
            throw new AiUnavailableException('AI service returned an empty response.');
        }

        return $content;
    }

    /**
     * Generate an embedding vector for the given text using Gemini's embedContent endpoint.
     *
     * Mirrors RagService::embed()'s signature so it can be swapped in as a drop-in replacement.
     *
     * @return float[]
     */
    public function embed(string $text): array
    {
        $model = config('services.gemini.embed_model');

        $response = Http::timeout(60)
            ->withHeaders(['x-goog-api-key' => config('services.gemini.key')])
            ->post(self::API_BASE . '/' . $model . ':embedContent', [
                'model'   => 'models/' . $model,
                'content' => ['parts' => [['text' => $text]]],
            ]);

        return $response->json('embedding.values') ?? [];
    }

    /**
     * Generate embeddings for multiple texts in a single Gemini request.
     *
     * Mirrors RagService::embedBatch()'s signature so it can be swapped in as a drop-in replacement.
     *
     * @param  string[]  $texts
     * @return float[][]
     */
    public function embedBatch(array $texts): array
    {
        if (empty($texts)) {
            return [];
        }

        $model = config('services.gemini.embed_model');

        $response = Http::timeout(120)
            ->withHeaders(['x-goog-api-key' => config('services.gemini.key')])
            ->post(self::API_BASE . '/' . $model . ':batchEmbedContents', [
                'requests' => array_map(fn (string $text): array => [
                    'model'   => 'models/' . $model,
                    'content' => ['parts' => [['text' => $text]]],
                ], $texts),
            ]);

        return array_column($response->json('embeddings') ?? [], 'values');
    }
}

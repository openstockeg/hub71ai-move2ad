<?php

namespace App\Services\OpenAI;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the OpenAI Responses API.
 *
 * We call the REST API directly (openai-php/laravel does not yet support Guzzle 8).
 */
class ResponsesClient
{
    /**
     * Send a request to POST /responses and return the decoded body.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     *
     * @throws RequestException
     */
    public function create(array $payload): array
    {
        $payload['model'] ??= config('services.openai.model');

        return $this->http()->post('/responses', $payload)->throw()->json();
    }

    /**
     * Concatenate all output_text parts of a response.
     *
     * @param  array<string, mixed>  $response
     */
    public static function text(array $response): string
    {
        return collect($response['output'] ?? [])
            ->where('type', 'message')
            ->flatMap(fn (array $message) => $message['content'] ?? [])
            ->where('type', 'output_text')
            ->pluck('text')
            ->implode('');
    }

    /**
     * Collect url_citation annotations from a response.
     *
     * @param  array<string, mixed>  $response
     * @return array<int, array{url: string, title: string|null}>
     */
    public static function citations(array $response): array
    {
        return collect($response['output'] ?? [])
            ->where('type', 'message')
            ->flatMap(fn (array $message) => $message['content'] ?? [])
            ->flatMap(fn (array $part) => $part['annotations'] ?? [])
            ->where('type', 'url_citation')
            ->map(fn (array $a) => ['url' => $a['url'], 'title' => $a['title'] ?? null])
            ->unique('url')
            ->values()
            ->all();
    }

    protected function http(): PendingRequest
    {
        return Http::baseUrl(config('services.openai.base_url'))
            ->withToken(config('services.openai.key'))
            ->acceptJson()
            ->timeout(config('services.openai.timeout'));
    }
}

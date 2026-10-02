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
        return collect(self::items($response['output'] ?? null))
            ->where('type', 'message')
            ->flatMap(fn (array $message) => self::items($message['content'] ?? null))
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
        return collect(self::items($response['output'] ?? null))
            ->where('type', 'message')
            ->flatMap(fn (array $message) => self::items($message['content'] ?? null))
            ->flatMap(fn (array $part) => self::items($part['annotations'] ?? null))
            ->where('type', 'url_citation')
            ->map(fn (array $a) => ['url' => $a['url'], 'title' => $a['title'] ?? null])
            ->unique('url')
            ->values()
            ->all();
    }

    /**
     * URLs the web_search tool actually returned or opened.
     * Search sources are only present when the request includes "web_search_call.action.sources".
     *
     * @param  array<string, mixed>  $response
     * @return array<int, string>
     */
    public static function searchUrls(array $response): array
    {
        return collect(self::items($response['output'] ?? null))
            ->where('type', 'web_search_call')
            ->flatMap(fn (array $call) => [
                $call['action']['url'] ?? null,
                ...array_column(self::items($call['action']['sources'] ?? null), 'url'),
            ])
            ->filter(fn ($url) => is_string($url) && $url !== '')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * The array items of a list from the decoded response; anything else (missing, malformed) is skipped.
     *
     * @return array<int, array<string, mixed>>
     */
    protected static function items(mixed $list): array
    {
        return is_array($list) ? array_values(array_filter($list, is_array(...))) : [];
    }

    protected function http(): PendingRequest
    {
        return Http::baseUrl(config('services.openai.base_url'))
            ->withToken(config('services.openai.key'))
            ->acceptJson()
            ->timeout(config('services.openai.timeout'));
    }
}

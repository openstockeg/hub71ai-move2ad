<?php

namespace App\Services;

/**
 * Curated grounding facts (database/data/facts.json).
 */
class Facts
{
    /** @var array<string, mixed>|null */
    protected ?array $data = null;

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->data ??= json_decode(file_get_contents(database_path('data/facts.json')), true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<int, string>
     */
    public function allowedDomains(): array
    {
        return $this->all()['allowed_domains'];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function facts(): array
    {
        return $this->all()['facts'];
    }

    /**
     * Compact JSON of the facts for use in a prompt.
     */
    public function forPrompt(): string
    {
        return json_encode(
            collect($this->facts())->map(fn (array $fact) => array_filter([
                'id' => $fact['id'],
                'claim' => $fact['claim'],
                'source_url' => $fact['source_url'],
                'source_type' => $fact['source_type'],
                'needs_verification' => $fact['needs_verification'] ?? null,
            ], fn ($value) => $value !== null))->all(),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );
    }

    /**
     * Whether a URL points at an official (allowed) domain.
     */
    public function isOfficial(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return collect($this->allowedDomains())
            ->contains(fn (string $domain) => $host === $domain || str_ends_with($host, '.'.$domain));
    }

    /**
     * Strip tracking params from model-provided source URLs, and drop any URL
     * that is neither a curated fact nor a web search result (i.e. made up).
     *
     * @param  array<string, mixed>  $content
     * @param  array<int, string>  $searchUrls
     * @return array<string, mixed>
     */
    public function keepKnownSources(array $content, array $searchUrls): array
    {
        $known = collect($this->facts())->pluck('source_url')
            ->merge($searchUrls)
            ->filter()
            ->map(fn (string $url) => self::urlKey($url))
            ->flip();

        array_walk_recursive($content, function (&$value, $key) use ($known) {
            if ($key === 'source_url' && is_string($value)) {
                $value = $known->has(self::urlKey($value)) ? self::cleanUrl($value) : null;
            }
        });

        return $content;
    }

    /**
     * Sources cited in generated content, for display: unique, with host and official flag.
     *
     * @param  array<string, mixed>|null  $content
     * @return array<int, array{url: string, host: string, official: bool}>
     */
    public function citedSources(?array $content): array
    {
        return collect($content ?? [])
            ->dot()
            ->filter(fn ($value, $key) => str_ends_with($key, 'source_url') && filled($value))
            ->unique()
            ->values()
            ->map(fn (string $url) => [
                'url' => $url,
                'host' => preg_replace('/^www\./', '', (string) parse_url($url, PHP_URL_HOST)),
                'official' => $this->isOfficial($url),
            ])
            ->all();
    }

    /**
     * Remove tracking parameters (utm_*, trk) from cited URLs.
     */
    public static function cleanUrl(string $url): string
    {
        $parts = parse_url($url);

        if (! isset($parts['query'])) {
            return $url;
        }

        parse_str($parts['query'], $query);
        $query = array_filter($query, fn ($key) => ! str_starts_with((string) $key, 'utm_') && $key !== 'trk', ARRAY_FILTER_USE_KEY);

        $base = strtok($url, '?');

        return $query ? $base.'?'.http_build_query($query) : $base;
    }

    /**
     * Comparable form of a URL: host without www, path without trailing slash, clean query.
     */
    public static function urlKey(string $url): string
    {
        $parts = parse_url(self::cleanUrl($url));
        $host = preg_replace('/^www\./', '', strtolower($parts['host'] ?? ''));
        $path = rtrim($parts['path'] ?? '', '/');

        return $host.$path.(isset($parts['query']) ? '?'.$parts['query'] : '');
    }
}

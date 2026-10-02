<?php

namespace App\Services;

use App\Services\OpenAI\ResponsesClient;

/**
 * Generates a personalised "Your Abu Dhabi" brief, grounded in curated facts
 * and web search restricted to official domains.
 */
class BriefGenerator
{
    public function __construct(
        protected ResponsesClient $client,
        protected Facts $facts,
    ) {}

    /**
     * @param  array{country: string, profession: string, experience_years: int, family: string, locale: string}  $profile
     * @return array{content: array<string, mixed>, model: string|null}
     */
    public function generate(array $profile): array
    {
        $response = $this->client->create([
            'instructions' => $this->instructions($profile['locale']),
            'input' => $this->input($profile),
            'reasoning' => ['effort' => config('services.openai.brief_effort')],
            'tools' => [[
                'type' => 'web_search',
                'filters' => ['allowed_domains' => $this->facts->allowedDomains()],
            ]],
            'include' => ['web_search_call.action.sources'],
            'text' => ['format' => [
                'type' => 'json_schema',
                'name' => 'abu_dhabi_brief',
                'strict' => true,
                'schema' => self::schema(),
            ]],
        ]);

        $content = json_decode(ResponsesClient::text($response), true, flags: JSON_THROW_ON_ERROR);

        return [
            'content' => $this->cleanSources($content, ResponsesClient::searchUrls($response)),
            'model' => $response['model'] ?? null,
        ];
    }

    protected function instructions(string $locale): string
    {
        $language = $locale === 'ar' ? 'Modern Standard Arabic' : 'English';

        return <<<PROMPT
        You are Move2AD, a trusted, independent guide for people abroad who are considering moving to Abu Dhabi (UAE) for work or to build a company.
        You are not a government service and must never claim to be one.

        Write a short, personalised brief for the person described. Rules:
        - Ground every claim in the FACTS below or in web search results from official domains. Never invent numbers, fees, salaries or laws.
        - Every item with a source_url must use a URL taken from FACTS or from a search result. Use null if you have no source.
        - Prefer source_type "official". If a fact is "secondary" or needs_verification, phrase it as "reported" and keep the number approximate.
        - Salary ranges are not in FACTS: only give one if an official/search source supports it, otherwise say ranges vary and set salary_range to null.
        - Be honest about fit: if a visa route is unlikely for this profile, say so and why.
        - Be concrete and practical, answer-first, no marketing language. Keep each detail to 1-2 sentences.
        - Always include at least one scam warning: in the UAE the employer pays all visa/recruitment costs (Federal Decree-Law 33/2021).
        - suggested_questions: 4 specific follow-up questions this person is likely to ask next. Write them complete and ready to ask — never use placeholders like [field] or [title].
        - If the profession is unclear or not a real profession, say so briefly in fit.reason and give general guidance for skilled professionals.
        - Write all text in {$language}. Keep URLs, numbers and AED amounts as-is.

        FACTS:
        {$this->facts->forPrompt()}
        PROMPT;
    }

    /**
     * @param  array{country: string, profession: string, experience_years: int, family: string, locale: string}  $profile
     */
    protected function input(array $profile): string
    {
        $family = match ($profile['family']) {
            'single' => 'moving alone',
            'couple' => 'moving with a spouse/partner',
            'family' => 'moving with a spouse and children',
            default => $profile['family'],
        };

        return "Profile: {$profile['profession']} from {$profile['country']}, {$profile['experience_years']} years of experience, {$family}. "
            .'They are still in their home country and want to know if and how to move to Abu Dhabi.';
    }

    /**
     * Strip tracking params from model-provided source URLs, and drop any URL
     * that is neither a curated fact nor a web search result (i.e. made up).
     *
     * @param  array<string, mixed>  $content
     * @param  array<int, string>  $searchUrls
     * @return array<string, mixed>
     */
    protected function cleanSources(array $content, array $searchUrls): array
    {
        $known = collect($this->facts->facts())->pluck('source_url')
            ->merge($searchUrls)
            ->filter()
            ->map(fn (string $url) => Facts::urlKey($url))
            ->flip();

        array_walk_recursive($content, function (&$value, $key) use ($known) {
            if ($key === 'source_url' && is_string($value)) {
                $value = $known->has(Facts::urlKey($value)) ? Facts::cleanUrl($value) : null;
            }
        });

        return $content;
    }

    /**
     * JSON schema for the structured brief.
     *
     * @return array<string, mixed>
     */
    public static function schema(): array
    {
        $source = ['type' => ['string', 'null']];

        $object = fn (array $properties) => [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => $properties,
            'required' => array_keys($properties),
        ];

        $list = fn (array $properties) => ['type' => 'array', 'items' => $object($properties)];

        return $object([
            'headline' => ['type' => 'string', 'description' => 'One-line personalised headline, e.g. "Software engineer from Cairo: a strong fit for Abu Dhabi"'],
            'summary' => ['type' => 'string', 'description' => '2-3 sentence answer-first summary.'],
            'fit' => $object([
                'level' => ['type' => 'string', 'enum' => ['strong', 'good', 'possible', 'challenging']],
                'reason' => ['type' => 'string'],
                'source_url' => $source,
            ]),
            'visa_paths' => $list([
                'name' => ['type' => 'string'],
                'duration' => ['type' => 'string'],
                'requirements' => ['type' => 'string'],
                'likelihood' => ['type' => 'string', 'enum' => ['likely', 'possible', 'unlikely']],
                'why' => ['type' => 'string', 'description' => 'Why this likelihood for this specific profile.'],
                'source_url' => $source,
            ]),
            'money' => $object([
                'salary_range' => ['type' => ['string', 'null'], 'description' => 'Sourced salary range, or null. Put salary caveats in note.'],
                'rent_1br' => ['type' => 'string', 'description' => 'Rent figures only — no salary information.'],
                'upfront_costs' => ['type' => 'string'],
                'note' => ['type' => 'string'],
                'source_url' => $source,
            ]),
            'steps' => $list([
                'stage' => ['type' => 'string', 'enum' => ['explore', 'visit', 'move', 'settle', 'build']],
                'title' => ['type' => 'string'],
                'detail' => ['type' => 'string'],
                'source_url' => $source,
            ]),
            'watch_out' => $list([
                'title' => ['type' => 'string'],
                'detail' => ['type' => 'string'],
                'source_url' => $source,
            ]),
            'suggested_questions' => ['type' => 'array', 'items' => ['type' => 'string']],
        ]);
    }
}

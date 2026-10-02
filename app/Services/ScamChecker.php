<?php

namespace App\Services;

use App\Services\OpenAI\ResponsesClient;

/**
 * Checks a pasted job offer (WhatsApp, email, LinkedIn…) for recruitment-scam
 * red flags, grounded in curated facts and web search on official domains.
 */
class ScamChecker
{
    public function __construct(
        protected ResponsesClient $client,
        protected Facts $facts,
    ) {}

    /**
     * @return array{content: array<string, mixed>, model: string|null}
     */
    public function check(string $message, string $locale): array
    {
        $response = $this->client->create([
            'instructions' => $this->instructions($locale),
            'input' => "Job offer message to check:\n\"\"\"\n{$message}\n\"\"\"",
            'reasoning' => ['effort' => config('services.openai.brief_effort')],
            'tools' => [[
                'type' => 'web_search',
                'filters' => ['allowed_domains' => $this->facts->allowedDomains()],
            ]],
            'include' => ['web_search_call.action.sources'],
            'text' => ['format' => [
                'type' => 'json_schema',
                'name' => 'job_offer_check',
                'strict' => true,
                'schema' => self::schema(),
            ]],
        ]);

        $content = json_decode(ResponsesClient::text($response), true, flags: JSON_THROW_ON_ERROR);

        return [
            'content' => $this->facts->keepKnownSources($content, ResponsesClient::searchUrls($response)),
            'model' => $response['model'] ?? null,
        ];
    }

    protected function instructions(string $locale): string
    {
        $language = $locale === 'ar' ? 'Modern Standard Arabic' : 'English';

        return <<<PROMPT
        You are Move2AD, a trusted, independent guide for people abroad who are considering a job in Abu Dhabi (UAE).
        You are not a government service and must never claim to be one.

        The user pasted a job offer or recruiter message they received. Check it for recruitment-scam red flags. Rules:
        - Ground every red flag in the FACTS below or in web search results from official domains. Never invent laws, fees or phone numbers.
        - Core rule: in the UAE the employer pays all visa and recruitment costs and may not recover them from the worker (Federal Decree-Law 33/2021). Any request for money from the candidate (visa, processing, training, medical, insurance, deposit, "refundable" fee) is a high-severity red flag.
        - Other signals: unsolicited offer, salary far above market, no interview, urgency/pressure, personal email (gmail/yahoo/outlook) or WhatsApp-only contact, no company details or trade licence, payment links or bank transfers to individuals, documents as images, spelling of the company name differing from the real one.
        - quote: copy the exact short phrase from the message that shows the flag (keep its original language). Use null if the flag is about something missing.
        - Do not over-claim: if the message has no clear red flags, say "no_red_flags_found" and explain how to verify the offer anyway. Never call an offer guaranteed safe.
        - verdict "likely_scam" when there is at least one high-severity flag (especially any request for money); "suspicious" for medium flags only.
        - next_steps: 2-4 concrete actions (e.g. do not pay, verify the employer and the offer through official channels, how to report). Each with a source_url when available.
        - Every source_url must be taken from FACTS or from a search result. Use null if you have no source.
        - If the text is not a job offer at all, set verdict "not_a_job_offer" and explain briefly.
        - Write all text in {$language} (except quotes). Keep URLs, numbers and AED amounts as-is.

        FACTS:
        {$this->facts->forPrompt()}
        PROMPT;
    }

    /**
     * JSON schema for the structured check result.
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
            'verdict' => ['type' => 'string', 'enum' => ['likely_scam', 'suspicious', 'no_red_flags_found', 'not_a_job_offer']],
            'headline' => ['type' => 'string', 'description' => 'One-line answer, e.g. "This offer asks you to pay a visa fee — that is illegal in the UAE."'],
            'summary' => ['type' => 'string', 'description' => '1-2 sentence explanation.'],
            'red_flags' => $list([
                'title' => ['type' => 'string'],
                'severity' => ['type' => 'string', 'enum' => ['high', 'medium', 'low']],
                'quote' => ['type' => ['string', 'null']],
                'explanation' => ['type' => 'string'],
                'source_url' => $source,
            ]),
            'good_signs' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Reassuring signals, if any. Can be empty.'],
            'next_steps' => $list([
                'title' => ['type' => 'string'],
                'detail' => ['type' => 'string'],
                'source_url' => $source,
            ]),
        ]);
    }
}

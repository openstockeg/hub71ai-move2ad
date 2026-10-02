<?php

namespace App\Console\Commands;

use App\Services\Facts;
use App\Services\OpenAI\ResponsesClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('openai:ping {--search : Also test web_search restricted to official domains}')]
#[Description('Verify the OpenAI API key, model and (optionally) domain-filtered web search')]
class OpenAIPing extends Command
{
    public function handle(ResponsesClient $client): int
    {
        if (blank(config('services.openai.key'))) {
            $this->error('OPENAI_API_KEY is not set in .env');

            return self::FAILURE;
        }

        $response = $client->create(['input' => 'Reply with exactly: Move2AD ready']);
        $this->info('Model '.($response['model'] ?? '?').': '.ResponsesClient::text($response));

        if ($this->option('search')) {

            $response = $client->create([
                'input' => 'What is the minimum monthly salary for the Abu Dhabi Golden Visa for skilled professionals? One sentence.',
                'tools' => [[
                    'type' => 'web_search',
                    'filters' => ['allowed_domains' => app(Facts::class)->allowedDomains()],
                ]],
            ]);

            $this->line(ResponsesClient::text($response));
            foreach (ResponsesClient::citations($response) as $citation) {
                $this->line('  - '.$citation['url']);
            }
        }

        return self::SUCCESS;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Services\Facts;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The dataset behind every answer, and the public index of answer pages for crawlers and AI assistants.
 */
class DataController extends Controller
{
    public function index(Facts $facts): Response
    {
        $answers = Answer::published();

        return Inertia::render('data/Index', [
            'locale' => 'en',
            'verified_at' => $facts->all()['meta']['verified_at'],
            'domains' => $facts->allowedDomains(),
            'facts' => collect($facts->facts())->map(fn (array $fact) => [
                'id' => $fact['id'],
                'category' => $fact['category'],
                'claim' => $fact['claim'],
                'source_url' => $fact['source_url'],
                'host' => preg_replace('/^www\./', '', (string) parse_url($fact['source_url'], PHP_URL_HOST)),
                'official' => $fact['source_type'] === 'official',
                'needs_verification' => $fact['needs_verification'] ?? false,
            ])->all(),
            'answers' => [
                'total' => $answers->count(),
                'asked' => (int) $answers->sum('asked_count'),
                'top' => $answers->take(12)->map(fn (Answer $answer) => [
                    'id' => $answer->public_id,
                    'question' => $answer->publicQuestion(),
                    'locale' => $answer->locale,
                    'asked_count' => $answer->asked_count,
                ])->all(),
            ],
        ])->withViewData(['meta' => [
            'title' => 'Our data',
            'description' => 'The curated, source-checked facts and official sources behind every Move2AD answer about moving to Abu Dhabi.',
            'indexable' => true,
        ]]);
    }

    public function sitemap(): HttpResponse
    {
        $urls = collect([route('home'), route('checks.create'), route('checks.create', ['lang' => 'ar']), route('data')])
            ->map(fn (string $url) => ['loc' => $url, 'lastmod' => null])
            ->merge(Answer::published()->map(fn (Answer $answer) => [
                'loc' => route('answers.show', $answer),
                'lastmod' => $answer->updated_at?->toAtomString(),
            ]));

        $xml = $urls->map(fn (array $url) => '<url><loc>'.e($url['loc']).'</loc>'
            .($url['lastmod'] ? '<lastmod>'.$url['lastmod'].'</lastmod>' : '').'</url>')->implode("\n");

        return response('<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n".$xml."\n</urlset>\n")
            ->header('Content-Type', 'application/xml');
    }

    /**
     * llms.txt (llmstxt.org): a plain index so AI assistants can find and cite the answer pages.
     */
    public function llms(): HttpResponse
    {
        $answers = Answer::published()
            ->map(fn (Answer $answer) => '- ['.str_replace(['[', ']'], '', $answer->publicQuestion()).']('.route('answers.show', $answer).')')
            ->implode("\n");

        $text = <<<'TXT'
        # Move2AD

        > Independent, source-cited answers for people deciding to move to Abu Dhabi: visas, jobs, rent, scams and settling in. Every answer is grounded in a curated dataset of facts and in web searches limited to official UAE and Abu Dhabi government domains. Move2AD is not a government service.

        - [Our data](%s): the curated facts, their sources, and the official domains answers may cite
        - [Check a job offer](%s): checks a job offer against UAE labour law and official scam warnings

        ## Answers

        %s

        TXT;

        return response(sprintf($text, route('data'), route('checks.create'), $answers ?: '- (none yet)'))
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function robots(): HttpResponse
    {
        return response("User-agent: *\nDisallow:\n\nSitemap: ".route('sitemap')."\n")
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}

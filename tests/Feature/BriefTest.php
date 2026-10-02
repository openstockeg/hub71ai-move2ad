<?php

use App\Models\Brief;
use App\Services\BriefGenerator;
use App\Services\Facts;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;

function fakeBriefContent(): array
{
    return [
        'headline' => 'Software engineer from Egypt: a good fit',
        'summary' => 'Get a job offer first.',
        'fit' => ['level' => 'good', 'reason' => 'In demand.', 'source_url' => 'https://www.adro.gov.ae/en/Visas?utm_source=openai'],
        'visa_paths' => [[
            'name' => 'Employer-sponsored work visa',
            'duration' => '2 years',
            'requirements' => 'A job offer.',
            'likelihood' => 'likely',
            'why' => 'Normal route.',
            'source_url' => 'https://www.adro.gov.ae/en/Visas',
        ]],
        'money' => [
            'salary_range' => null,
            'rent_1br' => 'AED 40k–60k',
            'upfront_costs' => '5% deposit',
            'note' => 'Varies.',
            'source_url' => 'https://www.gaiarealty.ae/blog/rent',
        ],
        'steps' => [['stage' => 'explore', 'title' => 'Apply', 'detail' => 'Apply from home.', 'source_url' => null]],
        'watch_out' => [['title' => 'Visa fee scam', 'detail' => 'Employer pays.', 'source_url' => 'https://uaelegislation.gov.ae/en/legislations/1541']],
        'suggested_questions' => ['Can my spouse work?'],
    ];
}

function fakeOpenAI(?array $content = null): void
{
    Http::fake([
        'api.openai.com/*' => Http::response([
            'model' => 'gpt-test',
            'output' => [
                [
                    'type' => 'web_search_call',
                    'action' => ['type' => 'search', 'query' => 'rent', 'sources' => [
                        ['type' => 'url', 'url' => 'https://adro.gov.ae/en/Visas/'],
                        ['type' => 'url', 'url' => 'https://www.gaiarealty.ae/blog/rent'],
                        ['type' => 'url', 'url' => 'https://uaelegislation.gov.ae/en/legislations/1541'],
                    ]],
                ],
                ['type' => 'web_search_call', 'action' => ['type' => 'open_page', 'url' => 'https://u.ae/en/jobseeker']],
                [
                    'type' => 'message',
                    'content' => [['type' => 'output_text', 'text' => json_encode($content ?? fakeBriefContent()), 'annotations' => []]],
                ],
            ],
        ]),
    ]);
}

test('home page shows the brief form', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('brief/Create'));
});

test('submitting the form generates a brief', function () {
    fakeOpenAI();

    $response = $this->post(route('briefs.store'), [
        'country' => 'Egypt',
        'profession' => 'Software engineer',
        'experience_years' => 6,
        'family' => 'couple',
        'locale' => 'en',
    ]);

    $brief = Brief::sole();
    $response->assertRedirect(route('briefs.show', $brief));
    expect($brief->status)->toBe(Brief::PENDING);
    Http::assertNothingSent();

    $this->postJson(route('briefs.generate', $brief))->assertJson(['status' => Brief::READY]);

    $brief->refresh();
    expect($brief->content['headline'])->toBe('Software engineer from Egypt: a good fit')
        ->and($brief->content['fit']['source_url'])->toBe('https://www.adro.gov.ae/en/Visas');

    Http::assertSent(fn ($request) => $request['text']['format']['type'] === 'json_schema'
        && in_array('adro.gov.ae', $request['tools'][0]['filters']['allowed_domains'])
        && $request['include'] === ['web_search_call.action.sources']);

    // A second call does not generate again.
    $this->postJson(route('briefs.generate', $brief))->assertJson(['status' => Brief::READY]);
    Http::assertSentCount(1);
});

test('brief page lists sources and marks official ones', function () {
    fakeOpenAI();
    $brief = Brief::create(['country' => 'Egypt', 'profession' => 'Nurse', 'experience_years' => 3, 'family' => 'single', 'locale' => 'en']);
    $brief->generate(app(BriefGenerator::class));

    $this->get(route('briefs.show', $brief))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('brief/Show')
            ->where('brief.status', 'ready')
            ->has('brief.sources', 3)
            ->where('brief.sources.0.official', true)
            ->where('brief.sources.1.host', 'gaiarealty.ae')
            ->where('brief.sources.1.official', false)
            ->where('brief.sources.2.official', true));
});

test('source urls not found in facts or search results are dropped', function () {
    $content = fakeBriefContent();
    $content['steps'][0]['source_url'] = 'https://u.ae/ar/made-up/jobseeker-visit-visa';
    $content['watch_out'][0]['source_url'] = 'https://u.ae/en/jobseeker?trk=public_post-text';
    $content['visa_paths'][0]['source_url'] = collect(app(Facts::class)->facts())->first()['source_url'];
    fakeOpenAI($content);

    $brief = Brief::create(['country' => 'Egypt', 'profession' => 'Nurse', 'experience_years' => 3, 'family' => 'single', 'locale' => 'ar']);
    $brief->generate(app(BriefGenerator::class));

    expect($brief->content['steps'][0]['source_url'])->toBeNull()
        ->and($brief->content['watch_out'][0]['source_url'])->toBe('https://u.ae/en/jobseeker')
        ->and($brief->content['visa_paths'][0]['source_url'])->toBe($content['visa_paths'][0]['source_url'])
        ->and($brief->content['fit']['source_url'])->toBe('https://www.adro.gov.ae/en/Visas');
});

test('a failed generation is stored as failed', function () {
    Http::fake(['api.openai.com/*' => Http::response(['error' => 'boom'], 500)]);
    $brief = Brief::create(['country' => 'Egypt', 'profession' => 'Nurse', 'experience_years' => 3, 'family' => 'single', 'locale' => 'en']);

    $brief->generate(app(BriefGenerator::class));

    expect($brief->fresh()->status)->toBe(Brief::FAILED);
});

test('brief form validates input', function () {
    $this->post(route('briefs.store'), ['family' => 'pets', 'locale' => 'fr'])
        ->assertSessionHasErrors(['country', 'profession', 'experience_years', 'family', 'locale']);
});

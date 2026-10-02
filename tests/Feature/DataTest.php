<?php

use App\Models\Answer;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;

function publishedAnswer(string $question, bool $publishable = true, string $status = 'ready'): Answer
{
    $answer = Answer::forQuestion($question, 'en');
    $answer->forceFill([
        'status' => $status,
        'content' => ['public_question' => "Clean: $question", 'publishable' => $publishable],
    ])->save();

    return $answer;
}

test('the data page shows the curated facts and only publishable answers', function () {
    $public = publishedAnswer('Is a job-seeker visa possible?');
    publishedAnswer('Private question with my phone 0501234567', publishable: false);
    publishedAnswer('Still generating question', status: 'generating');

    $this->get(route('data'))
        ->assertOk()
        ->assertSee('<title>Our data - ', false)
        ->assertDontSee('noindex')
        ->assertInertia(fn (Assert $page) => $page
            ->component('data/Index')
            ->has('facts', count(json_decode(file_get_contents(database_path('data/facts.json')), true)['facts']))
            ->where('answers.total', 1)
            ->where('answers.top.0.id', $public->public_id)
            ->where('answers.top.0.question', 'Clean: Is a job-seeker visa possible?')
        );
});

test('the sitemap and llms.txt list only publishable answer pages', function () {
    $public = publishedAnswer('Is a job-seeker visa possible?');
    $private = publishedAnswer('Private question with my phone 0501234567', publishable: false);

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml')
        ->assertSee(route('answers.show', $public))
        ->assertSee(route('data'))
        ->assertDontSee(route('answers.show', $private));

    $this->get('/llms.txt')
        ->assertOk()
        ->assertSee('[Clean: Is a job-seeker visa possible?]('.route('answers.show', $public).')', false)
        ->assertDontSee('0501234567');

    $this->get('/robots.txt')->assertOk()->assertSee('Sitemap: '.route('sitemap'));
});

test('the published list survives a serializing cache store', function () {
    // Production caches in the database; the array store used in tests never serializes.
    config(['cache.default' => 'file']);
    Cache::flush();
    $public = publishedAnswer('Is a job-seeker visa possible?');

    $this->get(route('data'))->assertOk();
    $this->get(route('data'))->assertOk()->assertInertia(fn (Assert $page) => $page->where('answers.top.0.id', $public->public_id));
    $this->get('/sitemap.xml')->assertOk()->assertSee(route('answers.show', $public));

    Cache::flush();
});

test('crawler files are cacheable and start no session', function () {
    foreach (['/sitemap.xml', '/llms.txt', '/robots.txt'] as $path) {
        $response = $this->get($path)->assertOk();

        expect($response->headers->getCookies())->toBeEmpty()
            ->and($response->headers->get('Cache-Control'))->toContain('public');
    }
});

test('the data page interface follows the requested language', function () {
    $this->get(route('data', ['lang' => 'ar']))
        ->assertOk()
        ->assertSee('<html lang="ar" dir="rtl"', false)
        ->assertSee('<title>بياناتنا ومصادرنا - ', false)
        ->assertInertia(fn (Assert $page) => $page->where('locale', 'ar')->where('links_checked_at', '2026-10-02'));
});

<?php

use App\Models\Answer;
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

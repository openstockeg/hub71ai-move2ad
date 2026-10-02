<?php

use Inertia\Testing\AssertableInertia as Assert;

test('home page renders in the requested language', function () {
    $this->get(route('home', ['lang' => 'ar']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('brief/Create')->where('locale', 'ar'));

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->where('locale', 'en'));
});

test('validation messages follow the form language', function () {
    $this->post(route('checks.store'), ['message' => 'too short', 'locale' => 'ar'])
        ->assertSessionHasErrors(['message' => 'يجب ألا يقل نص العرض عن 20 حرفًا.']);

    $this->post(route('checks.store'), ['message' => 'too short', 'locale' => 'en'])
        ->assertSessionHasErrors(['message' => 'The message field must be at least 20 characters.']);
});

test('missing pages show a branded 404 with a way back', function () {
    $this->get('/brief/does-not-exist')
        ->assertNotFound()
        ->assertSee('This page does not exist')
        ->assertSee('Get your Abu Dhabi brief');
});

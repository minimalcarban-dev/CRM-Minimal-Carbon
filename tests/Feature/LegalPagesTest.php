<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('serves the public privacy policy page', function () {
    $this->get(route('legal.privacy'))
        ->assertOk()
        ->assertSee('Privacy Policy');
});

it('serves the public terms of service page', function () {
    $this->get(route('legal.terms'))
        ->assertOk()
        ->assertSee('Terms of Service');
});

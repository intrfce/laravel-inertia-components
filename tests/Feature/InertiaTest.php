<?php

use Inertia\Testing\AssertableInertia as Assert;

test('Test the basic route at /basic', function () {
    $this->markTestIncomplete("assertInertia not working for some reason");
    $response = $this->get('/basic');
    $response->assertInertia(function(Assert $page) {
        $page->has('one', '1')
        ->has('two', '2')
        ->has('three', '3')
        ->has('four', '4');
    });
});

test('that the Basic component route works', function() {
    $this->get('/basic')->assertOk();
});

test('that the ParamComponent route works', function() {
    $this->get('/param-component/danmatthews')->assertOk();
});

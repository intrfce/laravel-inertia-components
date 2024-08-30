<?php

use Illuminate\Support\Facades\Route;
use Intrfce\InertiaComponents\Tests\Http\Inertia\ActionRoutes;
use Intrfce\InertiaComponents\Tests\Http\Inertia\Basic;

test('Test the "Basic" component\'s show method directly to ensure all the properties are exported as you\'d expect', function () {
    expect(
        (new Basic())->show()
    )
        ->toBeArray()
        ->toHaveKeys(['three', 'four'])
        ->and(
            Basic::getActionRoutesToRegister()
        )->toBeEmpty();
});

test("Ensure the routes have been registered for the 'Basic' component.", function() {

    $routes = collect(Route::getRoutes()->getRoutesByName())->keys()
    ->filter(fn ($r) => str_starts_with($r, 'basic.'));

    expect($routes->count())->toBe(4)
        ->and($routes->contains('basic.show'))->toBeTrue()
        ->and($routes->contains('basic.store'))->toBeTrue()
        ->and($routes->contains('basic.update'))->toBeTrue()
        ->and($routes->contains('basic.destroy'))->toBeTrue();
});

test("Ensure the routes have been registered for the 'ActionRoutes' component.", function() {

    $routes = collect(Route::getRoutes()->getRoutesByName())->keys()
    ->filter(fn ($r) => str_starts_with($r, 'action-routes.'));

    expect($routes->count())->toBe(9)
        ->and($routes->contains('action-routes.get-action'))->toBeTrue()
        ->and($routes->contains('action-routes.post-action'))->toBeTrue()
        ->and($routes->contains('action-routes.put-action'))->toBeTrue()
        ->and($routes->contains('action-routes.patch-action'))->toBeTrue()
        ->and($routes->contains('action-routes.delete-action'))->toBeTrue();
});

test('The "ActionRoutes" component\'s getActionRoutesToRegister static getActionRoutesToRegister to ensure it returns the methods defined with the attributes.', function() {
    $actions = ActionRoutes::getActionRoutesToRegister();
    expect($actions)->toHaveCount(5)
        ->and(collect($actions)->where('target_function', 'getAction'))->toHaveCount(1)
        ->and(collect($actions)->where('target_function', 'postAction'))->toHaveCount(1)
        ->and(collect($actions)->where('target_function', 'putAction'))->toHaveCount(1)
        ->and(collect($actions)->where('target_function', 'patchAction'))->toHaveCount(1)
        ->and(collect($actions)->where('target_function', 'deleteAction'))->toHaveCount(1);
});

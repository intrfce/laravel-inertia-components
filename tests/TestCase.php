<?php

namespace Intrfce\InertiaComponents\Tests;

use Intrfce\InertiaComponents\Providers\InertiaComponentsServiceProvider;
use Intrfce\InertiaComponents\Tests\Http\Inertia\ActionRoutes;
use Intrfce\InertiaComponents\Tests\Http\Inertia\Basic;
use Intrfce\InertiaComponents\Tests\Http\Inertia\ParamComponent;
use Intrfce\InertiaComponents\Tests\Providers\TestApplicationServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{

    protected function defineEnvironment($app)
    {
        config()->set('view.paths', [__DIR__.'/TestApp/resources/views']);
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string<\Illuminate\Support\ServiceProvider>>
     */
    protected function getPackageProviders($app): array
    {
        return [
            InertiaComponentsServiceProvider::class,
            TestApplicationServiceProvider::class,
        ];
    }

     /**
     * Define routes setup.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    protected function defineRoutes($router): void
    {
        $router->inertia('/basic', Basic::class)->name('basic');
        $router->inertia('/action-routes', ActionRoutes::class)->name('action-routes');
        $router->inertia('/param-component/{username}', ParamComponent::class)->name('param-component');
    }
}

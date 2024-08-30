<?php

namespace Intrfce\InertiaComponents\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Intrfce\InertiaComponents\Services\RouteRegistrationProxy;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class InertiaComponentsServiceProvider extends ServiceProvider
{
	public function register(): void
	{
        Route::macro('inertia', function($path, string $classComponent) {

            if (!class_exists($classComponent)) {
                throw new RouteNotFoundException("Class '$classComponent' not found.");
            }

            return new RouteRegistrationProxy($path, $classComponent);
        });
	}
	
	public function boot(): void
	{
	}
}

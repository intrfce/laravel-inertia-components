<?php

namespace Intrfce\InertiaComponents\Services;

use Exception;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;

/**
 * @mixin  Illuminate\Routing\Route;
 */
class RouteRegistrationProxy
{
    protected array $resources = ['show', 'store', 'update', 'destroy'];
    protected ?string $baseName = null;
    /**
     * Store any method calls here to be passed to the route definitions.
     */
    protected array $proxiedMethodCalls = [];

    public function __construct(public readonly string $path, public readonly string $classComponent) {}

    /**
     * Provide which sub-routes you want to define.
     * show() is not optional though.
     *
     * @param  string[]  $toRegister
     * @return void
     *
     * @throws Exception
     */
    public function only(array $toRegister): self
    {
        $accepts = collect($this->resources);
        $this->resources = collect($toRegister)
            ->unique()
            ->each(function ($route) use ($accepts) {
                if (! $accepts->contains($route)) {
                    throw new Exception("The route method '{$route}' is not recognised, only " . $accepts->join(',', 'and') . ' are accepted');
                }
            })
            ->push('show') // Always has to be there.
            ->toArray();

        return $this;
    }

    public function name(string $name): self
    {
        $this->baseName = $name;

        return $this;
    }

    public function __destruct()
    {
        foreach ($this->resources as $resource) {

            $baseDefinition = match ($resource) {
                'show' => RouteFacade::get($this->path, [$this->classComponent, 'showProxy']),
                'store' => RouteFacade::post($this->path, [$this->classComponent, 'storeProxy']),
                'update' => RouteFacade::patch($this->path, [$this->classComponent, 'updateProxy']),
                'destroy' => RouteFacade::delete($this->path, [$this->classComponent, 'destroyProxy']),
            };

            if ($this->baseName !== null) {
                $baseDefinition->name("{$this->baseName}.{$resource}");
            }

            if (! empty($this->proxiedMethodCalls)) {
                foreach ($this->proxiedMethodCalls as $method => $params) {
                    $baseDefinition = call_user_func_array($baseDefinition, $params);
                }
            }
        }

        // Register any action routes.
        foreach (call_user_func($this->classComponent.'::getActionRoutesToRegister') as $route) {
            RouteFacade::{$route['method']}(
                $this->path . '/' . $route['path'],
                [$route['target_class'], $route['target_function']],
            )->name($this->baseName . '.' . $route['name']);
        }

    }

    public function __call(string $method, array $parameters): self
    {
        $this->proxiedMethodCalls[$method] = $parameters;

        return $this;
    }
}

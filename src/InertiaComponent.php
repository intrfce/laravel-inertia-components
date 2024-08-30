<?php

namespace Intrfce\InertiaComponents;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Intrfce\InertiaComponents\Attributes\Http\DeleteAction;
use Intrfce\InertiaComponents\Attributes\Http\GetAction;
use Intrfce\InertiaComponents\Attributes\Http\PatchAction;
use Intrfce\InertiaComponents\Attributes\Http\PostAction;
use Intrfce\InertiaComponents\Attributes\Http\PutAction;
use Intrfce\InertiaComponents\Contacts\HttpActionContract;
use Intrfce\InertiaComponents\Data\ComponentMeta;
use Intrfce\InertiaComponents\Exceptions\MissingHttpMethodException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

abstract class InertiaComponent
{

    protected string $template;

    /**
     * Public methods that should _not_ be exported to the Inertia component.
     *
     * @var string[]
     */
    private static array $reservedMethodNames = [
        'show', 'store', 'destroy', 'update', 'showProxy', 'storeProxy', 'destroyProxy', 'updateProxy',
    ];

    /**
     * Scan for any public methods annotated with one of the HTTPAction attributes and
     * return an array of information about them so they can be registered by the
     * RouteRegisrationProxy class.
     *
     * @return array
     */
    public static function getActionRoutesToRegister(): array
    {
        $reflectionClass = new ReflectionClass(static::class);

        return collect($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC))
            ->reject(fn (ReflectionMethod $method) => str_starts_with($method->getName(), '__'))
            ->reject(fn (ReflectionMethod $method) => in_array($method->getName(), self::$reservedMethodNames))
            ->keyBy(fn (ReflectionMethod $method) => $method->getName())
            ->flatMap(function (ReflectionMethod $method) {
                return collect([GetAction::class, PostAction::class, PutAction::class, PatchAction::class, DeleteAction::class])
                    ->map(function ($methodAttributeClass) use ($method) {
                        $methodAttributes = $method->getAttributes($methodAttributeClass);
                        if (! empty($methodAttributes[0])) {

                            $methodClass = $methodAttributes[0]->getName();
                            /** @var HttpActionContract $method */
                            $httpMethod = (new $methodClass)->method();
                            $url = Str::kebab($method->getName());

                            return [
                                'method' => strtolower($httpMethod),
                                'path' => $url,
                                'name' => Str::snake($url, '_'),
                                'target_function' => $method->getName(),
                                'target_class' => static::class,
                            ];
                        } else {
                            return null;
                        }
                    })
                    ->values();
            })
            ->filter()
            ->all();
    }

    /**
     * @throws \Exception
     */
    protected function getTemplate(): string
    {
        if (!isset($this->template)) {
            throw new \Exception('No $template property has been set for this component');
        }
        return $this->template;
    }

    /**
     * @throws MissingHttpMethodException
     */
    public function showProxy(): mixed
    {
        return $this->buildResponse('show');
    }

    /**
     * @throws MissingHttpMethodException
     */
    public function storeProxy(): mixed
    {
        return $this->buildResponse('store');
    }

    /**
     * @throws MissingHttpMethodException
     */
    public function updateProxy(): mixed
    {
        return $this->buildResponse('udpate');
    }

    /**
     * @throws MissingHttpMethodException
     */
    public function destroyProxy(): mixed
    {
        return $this->buildResponse('destroy');
    }

    /**
     * @throws MissingHttpMethodException
     * @throws \Exception
     */
    protected function buildResponse(string $resource): mixed
    {
        $response = $this->callMethodIfExists($resource);

        // Instance of anything except an array or a collection? return it.
        if (! is_array($response)) {
            return $response;
        }

        // Use reflection to get ALL the `public` properties and methods, but exclude our known ones.
        $reflectionClass = new ReflectionClass($this);

        $publicProperties = collect($reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC));
        $publicPropertyNames = $publicProperties->map(fn (ReflectionProperty $property) => $property->getName());
        $propertyValues = collect($reflectionClass->getDefaultProperties())->filter(fn ($value, $name) => $publicPropertyNames->contains($name));

        $publicMethods = collect($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC))
            ->reject(fn (ReflectionMethod $method) => str_starts_with($method->getName(), '__'))
            ->reject(fn (ReflectionMethod $method) => in_array($method->getName(), self::$reservedMethodNames))
            ->keyBy(fn (ReflectionMethod $method) => $method->getName())
            ->map(function (ReflectionMethod $method) {
                if ($this->isPropertyMethodLazy($method->getName())) {
                    return Inertia::lazy([$this, $method->getName()]);
                }
                if ($this->isPropertyMethodAlways($method->getName())) {
                    return Inertia::always([$this, $method->getName()]);
                }

                return App::call([$this, $method->getName()]);
            });

        $merged = $publicMethods
            ->merge($propertyValues)
            ->merge(collect($response))
            ->merge(['component' => new ComponentMeta(
                request()->url(),
            )])
            ->toArray();

        return Inertia::render($this->getTemplate(), $merged);
    }

    /**
     * @throws MissingHttpMethodException
     */
    private function callMethodIfExists(string $method_name): mixed
    {
        if (method_exists($this, $method_name)) {
            return App::call([$this, $method_name], [...request()->route()->parameters()]);
        }
        throw new MissingHttpMethodException("Method {$method_name} does not exist.");
    }

    /**
     * Checks is a property method is marked as Lazy, which wraps it in an
     * Inertia::lazy call.
     */
    private function isPropertyMethodLazy(string $method_name): bool
    {
        $reflectionClass = new ReflectionClass($this);
        $attributes = $reflectionClass->getMethod($method_name)->getAttributes(Lazy::class);

        return isset($attributes[0]);
    }

    private function isPropertyMethodAlways(string $method_name): bool
    {
        $reflectionClass = new ReflectionClass($this);
        $attributes = $reflectionClass->getMethod($method_name)->getAttributes(Always::class);

        return isset($attributes[0]);
    }
}

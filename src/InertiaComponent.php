<?php
namespace Intrfce\InertiaComponents;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Intrfce\InertiaComponents\Attributes\Http\GetAction;
use Intrfce\InertiaComponents\Attributes\Http\PostAction;
use Intrfce\InertiaComponents\Contacts\HttpActionContract;
use Intrfce\InertiaComponents\Data\ComponentMeta;
use Intrfce\InertiaComponents\Exceptions\MissingHttpMethodException;
use ReflectionClassConstant;
use Symfony\Component\HttpFoundation\Request;

abstract class InertiaComponent {

    /**
     * @var string[]
     */
    public static array $registeredResourceRoutes = [];

    private static array $reservedMethodNames = [
        'show','store','destroy','update'
    ];

    public function __invoke(...$args)
    {
        return $this->buildResponse(...$args);
    }

    /**
     * @throws MissingHttpMethodException
     */
    private function buildResponse(...$args): mixed
    {
        $response =  match(strtoupper(request()->method())) {
            'GET', 'HEAD' => $this->callMethodIfExists('show', ...$args),
            'POST' => $this->callMethodIfExists('store', ...$args),
            'DELETE' => $this->callMethodIfExists('destroy', ...$args),
            'PUT','PATCH' => $this->callMethodIfExists('update', ...$args),
        };

        // Instance of anything except an array or a collection? return it.
        if (!is_array($response)) {
            return $response;
        };

        // Use reflection to get ALL the `public` properties and methods, but exclude our known ones.
        $reflectionClass = new \ReflectionClass($this);

        $publicProperties = collect($reflectionClass->getProperties(\ReflectionProperty::IS_PUBLIC));
        $publicPropertyNames = $publicProperties->map(fn (\ReflectionProperty $property) => $property->getName());
        $propertyValues = collect($reflectionClass->getDefaultProperties())->filter(fn ($value, $name) => $publicPropertyNames->contains($name));

        $publicMethods = collect($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC))
            ->reject(fn (\ReflectionMethod $method) => str_starts_with($method->getName(), '__'))
            ->reject(fn (\ReflectionMethod $method) => in_array($method->getName(), self::$reservedMethodNames))
            ->keyBy(fn (\ReflectionMethod $method) => $method->getName())
            ->map(function (\ReflectionMethod $method) {
                if ($this->isPropertyMethodLazy($method->getName())) {
                    return Inertia::lazy([$this, $method->getName()]);
                }
                if ($this->isPropertyMethodAlways($method->getName())) {
                    return Inertia::always([$this, $method->getName()]);
                }
                return App::call([$this, $method->getName()]);
            });

        // @TODO inject our component DTO.
        $merged = $publicMethods
            ->merge($propertyValues)
            ->merge(collect($response))
            ->merge(['component' => new ComponentMeta(
                request()->url(),
            )])
            ->toArray();
        return Inertia::render($this->template, $merged);
    }

    /**
     * @return array
     */
    public static function getActionRoutesToRegister(): array
    {
        $reflectionClass = new \ReflectionClass(static::class);

        return collect($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC))
            ->reject(fn (\ReflectionMethod $method) => str_starts_with($method->getName(), '__'))
            ->reject(fn (\ReflectionMethod $method) => in_array($method->getName(), self::$reservedMethodNames))
            ->keyBy(fn (\ReflectionMethod $method) => $method->getName())
            ->flatMap(function (\ReflectionMethod $method) {
                return collect([GetAction::class, PostAction::class])
                    ->map(function($methodAttributeClass) use ($method) {
                        $methodAttributes = $method->getAttributes($methodAttributeClass);
                        if (!empty($methodAttributes[0])) {

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
     *
     * @param string $method_name
     * @return bool
     */
    private function isPropertyMethodLazy(string $method_name): bool
    {
        $reflectionClass = new \ReflectionClass($this);
        $attributes = $reflectionClass->getMethod($method_name)->getAttributes(Lazy::class);
        return isset($attributes[0]);
    }

    private function isPropertyMethodAlways(string $method_name): bool
    {
        $reflectionClass = new \ReflectionClass($this);
        $attributes = $reflectionClass->getMethod($method_name)->getAttributes(Always::class);
        return isset($attributes[0]);
    }
}

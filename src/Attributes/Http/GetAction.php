<?php

namespace Intrfce\InertiaComponents\Attributes\Http;
use Intrfce\InertiaComponents\Contacts\HttpActionContract;

#[\Attribute]
class GetAction implements HttpActionContract
{
    public function __construct(?string $path = null)
    {
    }

    public function method(): string
    {
        return 'GET';
    }
}
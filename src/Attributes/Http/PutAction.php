<?php

namespace Intrfce\InertiaComponents\Attributes\Http;

use Attribute;
use Intrfce\InertiaComponents\Contacts\HttpActionContract;

#[Attribute]
class PutAction implements HttpActionContract
{
    public function __construct(?string $path = null) {}

    public function method(): string
    {
        return 'PUT';
    }
}

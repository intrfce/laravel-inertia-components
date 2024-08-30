<?php

namespace Intrfce\InertiaComponents\Tests\Http\Inertia;

use Intrfce\InertiaComponents\InertiaComponent;

class Basic extends InertiaComponent
{
    protected string $template = 'Basic';

    public function one()
    {
        return '1';
    }

    public function two()
    {
        return '2';
    }

    public function show(): array
    {
        return [
            'three' => '3',
            'four' => '4',
        ];
    }
}
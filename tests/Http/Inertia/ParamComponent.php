<?php

namespace Intrfce\InertiaComponents\Tests\Http\Inertia;

use Illuminate\Http\Request;
use Intrfce\InertiaComponents\Attributes\Http\DeleteAction;
use Intrfce\InertiaComponents\Attributes\Http\GetAction;
use Intrfce\InertiaComponents\Attributes\Http\PatchAction;
use Intrfce\InertiaComponents\Attributes\Http\PostAction;
use Intrfce\InertiaComponents\Attributes\Http\PutAction;
use Intrfce\InertiaComponents\InertiaComponent;

class ParamComponent extends InertiaComponent
{

    protected string $template = 'ParamComponent';

    public function show(Request $request, string $username): array
    {
        return [
            'username' => $username,
        ];
    }
}
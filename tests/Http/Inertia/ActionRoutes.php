<?php

namespace Intrfce\InertiaComponents\Tests\Http\Inertia;

use Intrfce\InertiaComponents\Attributes\Http\DeleteAction;
use Intrfce\InertiaComponents\Attributes\Http\GetAction;
use Intrfce\InertiaComponents\Attributes\Http\PatchAction;
use Intrfce\InertiaComponents\Attributes\Http\PostAction;
use Intrfce\InertiaComponents\Attributes\Http\PutAction;
use Intrfce\InertiaComponents\InertiaComponent;

class ActionRoutes extends InertiaComponent
{

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


      #[GetAction]
    public function getAction()
    {

    }

    #[PostAction]
    public function postAction()
    {

    }
    #[PutAction]
    public function putAction()
    {

    }

    #[PatchAction]
    public function patchAction()
    {

    }

    #[DeleteAction]
    public function deleteAction()
    {

    }
}
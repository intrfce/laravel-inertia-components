# Inertia Components

> [!WARNING]  
> This package is in a pre-release state and the API may change.

This package allows you to create Livewire-style class components for your InertiaJS applications.

## Features:

- ✅ Define HTTP-named resourceful style methods for `show`/`store`/`update`/`destroy` right in the same class.
- ✅ Any `public` properties or methods pass data back to your components.
- ✅ Attributes to mark public methods as `#[Lazy]` or `#[Always]`
- ⌛ Create components with `php artisan make:inertia`
- ⌛ `Route::inertia` helper.

## Why?

- Better organisation and encapsulation of code.
- Reduce clutter in your route files.

## HTTP Methods.

The base class reserves four `public` method names to use and their corresponding HTTP methods:

| Method Name | HTTP Method |
|-------------|  -----      |
 | show        | GET         |
| store       | POST |
| update      | PATCH/PUT |
 | destroy | DELETE |

Each of these methods behaves just like a controller method should, so you can inject route parameters and dependencies as needed:

```php
public function show(Request $request, string $id, ModelName $model): array {}
```

### Returning data from `show()`

The `show` method is used when your route is hit with a `GET` request.

If you return an `array` from this method, it will be merged with the rest of the public properties and methods, you can also
use the usual `Inertia::lazy` and `Inertia::always` closures from here too.





```php
<?php

class Login {

    protected $template = 'Auth/Login';

    public function show(Request $request): array
    {
        return [
           
        ];
    }
    
    public function store()
    {
        $data = request()->validate([
            'email' => 'required',
            'password' => 'required|confirmed',
        ]);
        
        $authenticated = \Illuminate\Support\Facades\Auth::attempt([
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
        
        if ($authenticated) {
            return redirect('dashboard');
        } else {
            return redirect()->back()->withErrors(['Your username and password were incorrect.']);
        }
    
    }

}
```

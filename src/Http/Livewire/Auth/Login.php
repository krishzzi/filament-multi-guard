<?php

namespace Iotronlab\FilamentMultiGuard\Http\Livewire\Auth;

use Filament\Http\Livewire\Auth\Login as FilamentLogin;
use Illuminate\Contracts\View\View;
class Login extends FilamentLogin
{



    public function render(): View
    {


        return \view('filament-multi-guard::login')->layout("filament::components.layouts.base", [
            "title" => __("filament::login.title"),
        ]);

    }

}

<?php

namespace Iotronlab\FilamentMultiGuard\Http\Livewire\Auth;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Iotronlab\FilamentMultiGuard\FilamentMultiGuard;
use Livewire\Component;
use Filament\Facades\Filament;

use Filament\Forms;
class Register extends Component implements HasForms
{

    use InteractsWithForms, WithRateLimiting;


    public ?string $heading = 'Signup';
    public string $currentPath;
    public string  $currentContext;

    public $name;
    public $email;
    public $password;
    public $password_confirm;


    public function mount()
    {
        if (Filament::auth()->check()) {
            return redirect(config("filament.home_url"));
        }

        $this->currentContext = Filament::currentContext();
        $this->currentPath = config(Filament::currentContext())['path'];


    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->label(__('Name'))
                ->required(),
            Forms\Components\TextInput::make('email')
                ->label(__('Email'))
                ->required()
                ->email()
                ->unique(table: config('filament-breezy.user_model')),
            Forms\Components\TextInput::make('password')
                ->label(__('Password'))
                ->required()
                ->password()
                ->rules(app(FilamentMultiGuard::class)->getPasswordRules()),
            Forms\Components\TextInput::make('password_confirm')
                ->label(__('Confirm Password'))
                ->required()
                ->password()
                ->same('password'),
        ];
    }


    protected function prepareModelData($data): array
    {
        $preparedData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ];

        return $preparedData;
    }

    public function register()
    {

        $currentGuard = config($this->currentContext)['auth']['guard'];
        $modelProvider = config('auth.guards')[$currentGuard]['provider'];
        $model = config('auth.providers')[$modelProvider]['model'];

        $preparedData = $this->prepareModelData($this->form->getState());
        $newUser = $model::create($preparedData);
        Filament::auth()->login($newUser, true);

        return redirect()->route($this->currentContext.'.pages.dashboard');

    }







    public function render(): View
    {
        return \view('filament-multi-guard::register')->layout('filament::components.layouts.base', [
            'title' => __('Register'),
        ]);

    }

}

<?php

namespace Iotronlab\FilamentMultiGuard\Http\Livewire\Auth;

use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action as NotificationAction;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Iotronlab\FilamentMultiGuard\FilamentMultiGuard;
use Livewire\Component;

class Forget extends Component implements HasForms
{

    use InteractsWithForms;

    public bool $hasBeenSent = false;
    public bool $isResetting = false;

    public string $currentPath;
    public string  $currentContext;

    public $email;
    public $token;
    public $password;
    public $password_confirm;

    public function mount($token = null): void
    {
        $this->currentContext = Filament::currentContext();
        $this->currentPath = config(Filament::currentContext())['path'];

        if (! is_null($token)) {
            // Verify that the token is valid before moving further.
            $this->email = request()->query('email', '');
            $this->token = $token;
            $this->isResetting = true;
        }
    }


    protected function getFormSchema(): array
    {
        if ($this->isResetting) {
            return [
                Forms\Components\TextInput::make("password")
                    ->label(__("Password"))
                    ->required()
                    ->password()
                    ->rules(app(FilamentMultiGuard::class)->getPasswordRules()),
                Forms\Components\TextInput::make("password_confirm")
                    ->label(__("Password confirm"))
                    ->required()
                    ->password()
                    ->same("password"),
            ];
        } else {
            return [
                Forms\Components\TextInput::make("email")
                    ->label(__("Email"))
                    ->required()
                    ->email()
                    ->exists(table: config('filament-breezy.user_model')),
            ];
        }
    }




    public function submit()
    {
        $data = $this->form->getState();

        $currentGuard = config($this->currentContext)['auth']['guard'];
        $broker = config('auth.guards')[$currentGuard]['provider'];


        //$broker = config('filament-breezy.reset_broker', config('auth.defaults.passwords'));

        if ($this->isResetting) {
            $response = Password::broker($broker)->reset([
                'token' => $this->token,
                'email' => $this->email,
                'password' => $data['password'],
            ],function ($user, $password) {
                $user->password = Hash::make($password);
                $user->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            });


            if ($response == Password::PASSWORD_RESET) {
                return redirect(route($this->currentContext.'.auth.login', ['email' => $this->email,'reset' => true]));
            }else{
                Notification::make()
                    ->title(__('Error resetting password. Please request a new password reset.'))
                    ->persistent()
                    ->actions([
                        NotificationAction::make('resetAgain')
                            ->label(__('Try Again'))
                            ->url(route($this->currentContext.'password.request')) // here
                    ])
                    ->danger()
                    ->send();
            }



        }else {

            $response = Password::broker($broker)->sendResetLink(['email' => $this->email]);

            if ($response === Password::RESET_LINK_SENT) {
                Notification::make()
                    ->title(__('Check your inbox for instructions!'))
                    ->success()
                    ->send();

                $this->hasBeenSent = true;
            } else {
                Notification::make()
                    ->title(match ($response) {
                        'passwords.throttled' => __('passwords.throttled'),
                        'passwords.user' => __('passwords.user')
                    })
                    ->danger()
                    ->send();
            }

        }



    }




















    public function render(): View
    {
        $view = view("filament-multi-guard::reset-password");

        $view->layout("filament::components.layouts.base", [
            "title" => __("filament-breezy::default.reset_password.title"),
        ]);

        return $view;
    }


}

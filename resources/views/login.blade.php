<div @class([
    'flex items-center justify-center min-h-screen bg-gray-100 text-gray-900 filament-breezy-auth-component filament-login-page',
    'dark:bg-gray-900 dark:text-white' => config('filament.dark_mode'),
])>
    <div
        class="px-6 -mt-16 md:mt-0 md:px-2 max-w-{{ config('filament-breezy.auth_card_max_w') ?? 'md' }} space-y-8 w-screen">
        <form wire:submit.prevent="authenticate" @class([
            'p-8 space-y-8 bg-white/50 backdrop-blur-xl border border-gray-200 shadow-2xl rounded-2xl relative filament-breezy-auth-card',
            'dark:bg-gray-900/50 dark:border-gray-700' => config('filament.dark_mode'),
        ])>

            <div class="w-full flex justify-center">
                <x-filament::brand />
            </div>
            <div>
                <h2 class="font-bold tracking-tight text-center text-2xl">
                    {{ __($heading) }}
                </h2>
                    <p class="mt-2 text-sm text-center">
                        {{  __('Don\'t have an account?') }}

                        @if(\Illuminate\Support\Facades\Route::has($currentContext.'.auth.register'))
                            <a href="{{route($currentContext.'.auth.register')}}" class="text-primary-600">Register</a>
                        @endif
                    </p>
            </div>
            {{ $this->form }}
            <x-filament::button type="submit" class="w-full" form="authenticate">
                {{ __('filament::login.buttons.submit.label') }}
            </x-filament::button>
            <div class="text-center">
                @if(\Illuminate\Support\Facades\Route::has($currentContext.'.password.request'))
                    <a href="{{route($currentContext.'.password.request')}}" class="text-primary-600 hover:text-primary-700">Forgot password?</a>
                @endif
            </div>
        </form>

        {{ $this->modal }}
        <x-filament::footer />
    </div>

    @livewire('notifications')

</div>


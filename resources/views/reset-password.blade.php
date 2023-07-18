
<div @class([
    'flex items-center justify-center min-h-screen bg-gray-100 text-gray-900 filament-breezy-auth-component filament-login-page',
    'dark:bg-gray-900 dark:text-white' => config('filament.dark_mode'),
])>

    <div
        class="px-6 -mt-16 md:mt-0 md:px-2 max-w-{{ config('filament-breezy.auth_card_max_w') ?? 'md' }} space-y-8 w-screen">
        <form wire:submit.prevent="submit" @class([
            'p-8 space-y-8 bg-white/50 backdrop-blur-xl border border-gray-200 shadow-2xl rounded-2xl relative filament-breezy-auth-card',
            'dark:bg-gray-900/50 dark:border-gray-700' => config('filament.dark_mode'),
        ])>

            <div class="w-full flex justify-center">
                <x-filament::brand />
            </div>

            <div>
                <h2 class="font-bold tracking-tight text-center text-2xl">
                    {{ __('Reset your password') }}
                </h2>
                <p class="mt-2 text-sm text-center">
                    {{ __('Or') }}
                    <a class="text-primary-600" href="{{route($currentContext.'.auth.login')}}">
                        {{ strtolower(__('filament::login.heading')) }}
                    </a>
                </p>
            </div>

            @unless($hasBeenSent)
                {{ $this->form }}

                <x-filament::button type="submit" class="w-full" form="submit">
                    {{ __('Submit') }}
                </x-filament::button>
            @else
                <span class="block text-center text-success-600 font-semibold">{{ __('Check your inbox for instructions!') }}</span>
            @endunless

        </form>

        {{ $this->modal }}
        <x-filament::footer />
    </div>

    @livewire('notifications')

</div>


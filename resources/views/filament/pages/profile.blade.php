<x-filament::page>

    <div class="grid grid-cols-2 gap-6 filament-breezy-grid-section">
        <div class="col-span-2 sm:col-span-1 flex justify-between">
            <div class="px-4 sm:px-0">
                <h3 @class(['text-lg font-medium text-gray-900 filament-breezy-grid-title','dark:text-white'=>config('filament.dark_mode')])>Update Profile</h3>

                <p @class(['mt-1 text-sm text-gray-600 filament-breezy-grid-description','dark:text-gray-100'=>config('filament.dark_mode')])>
                    {{$description ?? ''}}
                </p>
            </div>
        </div>
        <form wire:submit.prevent="updateProfile" class="col-span-2 sm:col-span-1 mt-5 md:mt-0">
            <x-filament::card>
                {{ $this->updateProfileForm }}
                <x-slot name="footer">
                    <div class="text-right">
                        <x-filament::button type="submit" form="updateProfile">
                            {{ __('Save Profile') }}
                        </x-filament::button>
                    </div>
                </x-slot>
            </x-filament::card>
        </form>
    </div>



    <x-filament::hr />

    <div class="grid grid-cols-2 gap-6 filament-breezy-grid-section">
        <div class="col-span-2 sm:col-span-1 flex justify-between">
            <div class="px-4 sm:px-0">
                <h3 @class(['text-lg font-medium text-gray-900 filament-breezy-grid-title','dark:text-white'=>config('filament.dark_mode')])>Update Password</h3>

                <p @class(['mt-1 text-sm text-gray-600 filament-breezy-grid-description','dark:text-gray-100'=>config('filament.dark_mode')])>
                    {{$description ?? ''}}
                </p>
            </div>
        </div>
        <form wire:submit.prevent="updatePassword" class="col-span-2 sm:col-span-1 mt-5 md:mt-0">
            <x-filament::card>

                {{ $this->updatePasswordForm }}

                <x-slot name="footer">
                    <div class="text-right">
                        <x-filament::button type="submit" form="updateProfile">
                            {{ __('Save Password') }}
                        </x-filament::button>
                    </div>
                </x-slot>
            </x-filament::card>
        </form>
    </div>


</x-filament::page>

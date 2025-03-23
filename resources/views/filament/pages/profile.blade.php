<x-filament-panels::page>
    <div class="space-y-6">
        <form wire:submit="updateProfile" class="space-y-8">
            {{ $this->form }}
            
            <div class="flex items-center gap-4 pt-3">
                <x-filament::button type="submit">
                    {{ __('Save') }}
                </x-filament::button>
                
                @if (session('status') === 'profile-updated')
                    <p
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 2000)"
                        class="text-sm text-gray-600 dark:text-gray-400"
                    >{{ __('Saved.') }}</p>
                @endif
            </div>
        </form>
    </div>
</x-filament-panels::page>

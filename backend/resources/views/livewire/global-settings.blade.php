<div>
    <x-filament::modal id="global-settings" slide-over width="3xl" :close-by-clicking-away="false">
        <x-slot name="trigger">
            <x-filament::button color="gray" size="sm" icon="heroicon-o-cog-6-tooth" outlined>
                Site settings
            </x-filament::button>
        </x-slot>

        <x-slot name="heading">Site settings</x-slot>
        <x-slot name="description">Logo, brand and options that apply to the whole website.</x-slot>

        <form wire:submit="save" class="fi-global-settings-form">
            {{ $this->form }}

            <div class="mt-6 flex items-center gap-3">
                <x-filament::button type="submit" wire:loading.attr="disabled">Save changes</x-filament::button>
                <x-filament::button color="gray" x-on:click="$dispatch('close-modal', { id: 'global-settings' })">Cancel</x-filament::button>
            </div>
        </form>
    </x-filament::modal>
</div>

<?php

namespace App\Livewire;

use App\Filament\Support\BrandFields;
use App\Models\Setting;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Site-wide options (logo, favicon, brand, tracking) editable from a slide-over
 * that opens from the top bar of every admin page.
 */
class GlobalSettings extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(Setting::ensure()->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(BrandFields::schema())
            ->statePath('data')
            ->model(Setting::ensure());
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $setting = Setting::ensure();
        $setting->update($data);
        $this->form->model($setting)->saveRelationships();

        Notification::make()->title('Site settings saved')->success()->send();

        $this->dispatch('close-modal', id: 'global-settings');
        $this->dispatch('site-settings-saved');
    }

    public function render(): View
    {
        return view('livewire.global-settings');
    }
}

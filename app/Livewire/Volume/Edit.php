<?php

namespace App\Livewire\Volume;

use App\Livewire\Forms\VolumeForm;
use App\Models\Volume;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Edit extends Component
{
    use AuthorizesRequests;

    public VolumeForm $form;

    public function mount(Volume $volume)
    {
        $this->authorize('update', $volume);

        $this->form->setVolume($volume);
    }

    public function save()
    {
        $this->authorize('update', $this->form->volume);

        $this->form->update();

        session()->flash('status', 'Volume updated successfully!');

        return $this->redirect(route('volumes.index'), navigate: true);
    }

    public function testConnection()
    {
        $this->form->testConnection();
    }

    public function render()
    {
        return view('livewire.volume.edit')
            ->layout('components.layouts.app', ['title' => __('Edit Volume')]);
    }
}

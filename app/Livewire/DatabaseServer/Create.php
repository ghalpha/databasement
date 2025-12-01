<?php

namespace App\Livewire\DatabaseServer;

use App\Livewire\Forms\DatabaseServerForm;
use App\Models\DatabaseServer;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Create extends Component
{
    use AuthorizesRequests;

    public DatabaseServerForm $form;

    public function mount(): void
    {
        $this->authorize('create', DatabaseServer::class);
    }

    public function save()
    {
        $this->authorize('create', DatabaseServer::class);

        if ($this->form->store()) {
            session()->flash('status', 'Database server created successfully!');

            return $this->redirect(route('database-servers.index'), navigate: true);
        }

        return false;
    }

    public function testConnection()
    {
        $this->form->testConnection();
    }

    public function render()
    {
        return view('livewire.database-server.create')
            ->layout('components.layouts.app', ['title' => __('Create Database Server')]);
    }
}

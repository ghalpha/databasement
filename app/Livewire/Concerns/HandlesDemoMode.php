<?php

namespace App\Livewire\Concerns;

trait HandlesDemoMode
{
    protected function abortIfDemoMode(string $redirectRoute): bool
    {
        if (auth()->user()?->isDemo()) {
            session()->flash('demo_notice', __('Demo mode is enabled. Changes cannot be saved.'));
            $this->redirect(route($redirectRoute), navigate: true);

            return true;
        }

        return false;
    }
}

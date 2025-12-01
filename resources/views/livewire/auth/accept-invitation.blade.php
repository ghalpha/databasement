<div class="flex flex-col gap-6">
    <div class="flex w-full flex-col text-center">
        <h1 class="text-2xl font-bold">{{ __('Complete your registration') }}</h1>
        <p class="text-sm opacity-70">{{ __('Welcome, :name! Set your password to complete your account setup.', ['name' => $user->name]) }}</p>
    </div>

    <form wire:submit="accept" class="flex flex-col gap-6">
        <!-- Email (read-only) -->
        <x-input
            label="{{ __('Email address') }}"
            type="email"
            :value="$user->email"
            readonly
            disabled
        />

        <!-- Password -->
        <x-password
            wire:model="password"
            label="{{ __('Password') }}"
            required
            autofocus
            autocomplete="new-password"
            placeholder="{{ __('Choose a strong password') }}"
        />

        <!-- Confirm Password -->
        <x-password
            wire:model="password_confirmation"
            label="{{ __('Confirm password') }}"
            required
            autocomplete="new-password"
            placeholder="{{ __('Confirm your password') }}"
        />

        <div class="flex items-center justify-end">
            <x-button type="submit" class="btn-primary w-full" label="{{ __('Complete registration') }}" spinner="accept" />
        </div>
    </form>
</div>

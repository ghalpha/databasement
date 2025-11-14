@props(['show' => false, 'title', 'message', 'onConfirm', 'onCancel'])

@if($show)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-zinc-500 bg-opacity-75 transition-opacity dark:bg-zinc-900 dark:bg-opacity-75" wire:click="{{ $onCancel }}"></div>

            <!-- Modal panel -->
            <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all dark:bg-zinc-800 sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                <div class="bg-white px-4 pb-4 pt-5 dark:bg-zinc-800 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-lg font-semibold leading-6 text-zinc-900 dark:text-zinc-100" id="modal-title">
                                {{ $title }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $message }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-zinc-50 px-4 py-3 dark:bg-zinc-900 sm:flex sm:flex-row-reverse sm:px-6">
                    <flux:button variant="primary" wire:click="{{ $onConfirm }}" class="w-full sm:ml-3 sm:w-auto bg-red-600 hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-700">
                        {{ __('Delete') }}
                    </flux:button>
                    <flux:button variant="ghost" wire:click="{{ $onCancel }}" class="mt-3 w-full sm:mt-0 sm:w-auto">
                        {{ __('Cancel') }}
                    </flux:button>
                </div>
            </div>
        </div>
    </div>
@endif

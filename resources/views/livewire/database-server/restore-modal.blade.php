<div>
    <x-modal wire:model="showModal" title="{{ __('Restore Database Snapshot') }}" box-class="max-w-3xl w-11/12 space-y-6" class="backdrop-blur">
        <p class="text-sm opacity-70">
            {{ __('Restore to:') }} <strong>{{ $targetServer?->name }}</strong>
        </p>

        <!-- Step Indicator -->
        <div class="mt-6 mb-8">
            <ul class="steps steps-horizontal w-full">
                <li class="step {{ $currentStep >= 1 ? 'step-primary' : '' }}">{{ __('Select Source') }}</li>
                <li class="step {{ $currentStep >= 2 ? 'step-primary' : '' }}">{{ __('Select Snapshot') }}</li>
                <li class="step {{ $currentStep >= 3 ? 'step-primary' : '' }}">{{ __('Destination') }}</li>
            </ul>
        </div>

        <!-- Step 1: Select Source Server -->
        @if($currentStep === 1)
            <div class="space-y-4">
                <p class="text-sm opacity-70">
                    {{ __('Select a source database server to restore from. Only servers with the same database type (:type) are shown.', ['type' => $targetServer?->database_type]) }}
                </p>

                @if($this->compatibleServers->isEmpty())
                    <div class="p-4 text-center border rounded-lg border-base-300">
                        <p class="opacity-70">{{ __('No compatible database servers with snapshots found.') }}</p>
                    </div>
                @else
                    <div class="space-y-2 max-h-96 overflow-y-auto" wire:loading.class="opacity-50 pointer-events-none" wire:target="selectSourceServer">
                        @foreach($this->compatibleServers as $server)
                            <div
                                wire:click="selectSourceServer('{{ $server->id }}')"
                                class="p-4 border rounded-lg cursor-pointer hover:bg-base-200 border-base-300 {{ $selectedSourceServerId === $server->id ? 'border-primary bg-primary/10' : '' }}"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="font-semibold">{{ $server->name }}</div>
                                        <div class="text-sm opacity-70">
                                            {{ $server->host }}:{{ $server->port }}
                                            @if($server->database_name)
                                                &bull; {{ $server->database_name }}
                                            @endif
                                        </div>
                                        @if($server->description)
                                            <div class="text-sm opacity-50 mt-1">{{ $server->description }}</div>
                                        @endif
                                    </div>
                                    <div class="ml-4 text-sm opacity-50 flex items-center gap-2">
                                        <x-loading wire:loading wire:target="selectSourceServer('{{ $server->id }}')" class="loading-xs" />
                                        {{ $server->snapshots->count() }} {{ Str::plural('snapshot', $server->snapshots->count()) }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        <!-- Step 2: Select Snapshot -->
        @if($currentStep === 2)
            <div class="space-y-4">
                <div class="flex items-center justify-between gap-4">
                    <p class="text-sm opacity-70">
                        {{ __('Select a snapshot to restore from') }} <strong>{{ $this->selectedSourceServer?->name }}</strong>.
                    </p>
                    <x-input
                        wire:model.live.debounce.300ms="snapshotSearch"
                        placeholder="{{ __('Search database...') }}"
                        icon="o-magnifying-glass"
                        clearable
                        class="w-64"
                    />
                </div>

                @if($this->paginatedSnapshots->isEmpty())
                    <div class="p-4 text-center border rounded-lg border-base-300">
                        <p class="opacity-70">
                            @if($snapshotSearch)
                                {{ __('No snapshots found matching ":search".', ['search' => $snapshotSearch]) }}
                            @else
                                {{ __('No completed snapshots found.') }}
                            @endif
                        </p>
                    </div>
                @else
                    <div class="space-y-1 max-h-80 overflow-y-auto" wire:loading.class="opacity-50 pointer-events-none" wire:target="selectSnapshot">
                        @foreach($this->paginatedSnapshots as $snapshot)
                            <div
                                wire:click="selectSnapshot('{{ $snapshot->id }}')"
                                class="px-3 py-2 border rounded-lg cursor-pointer hover:bg-base-200 border-base-300 {{ $selectedSnapshotId === $snapshot->id ? 'border-primary bg-primary/10' : '' }}"
                            >
                                <div class="flex items-center justify-between gap-2">
                                    <div class="font-medium text-sm">{{ $snapshot->database_name }}</div>
                                    <div class="text-xs opacity-60 whitespace-nowrap flex items-center gap-2">
                                        <x-loading wire:loading wire:target="selectSnapshot('{{ $snapshot->id }}')" class="loading-xs" />
                                        {{ $snapshot->created_at->diffForHumans() }} &bull; {{ $snapshot->getHumanFileSize() }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($this->paginatedSnapshots->hasPages())
                        <div class="pt-2">
                            {{ $this->paginatedSnapshots->links() }}
                        </div>
                    @endif
                @endif

                <div class="flex justify-start mt-4">
                    <x-button class="btn-ghost" wire:click="previousStep">
                        {{ __('Back') }}
                    </x-button>
                </div>
            </div>
        @endif

        <!-- Step 3: Enter Destination Schema -->
        @if($currentStep === 3)
            <div class="space-y-4">
                <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                    <x-input
                        wire:model.live.debounce.200ms="schemaName"
                        label="{{ __('Destination Database Name') }}"
                        placeholder="{{ __('Type or select database name...') }}"
                        @focus="open = true"
                        @keydown.escape="open = false"
                        autocomplete="off"
                    />
                    @error('schemaName')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror

                    <!-- Dropdown suggestions -->
                    @if(count($this->filteredDatabases) > 0)
                        <div
                            x-show="open"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg max-h-48 overflow-y-auto"
                        >
                            @foreach($this->filteredDatabases as $database)
                                <div
                                    wire:click="selectDatabase('{{ $database }}')"
                                    @click="open = false"
                                    class="px-3 py-2 cursor-pointer hover:bg-base-200 text-sm {{ $schemaName === $database ? 'bg-primary/10 font-medium' : '' }}"
                                >
                                    {{ $database }}
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if(in_array($schemaName, $existingDatabases))
                    <x-alert class="alert-warning" icon="o-exclamation-triangle">
                        The database <x-badge class="badge-error badge-dash" :value="$schemaName" /> already exists.<br>
                        {{ __('It will be overwritten if you continue.') }}
                    </x-alert>
                @endif

                @if($this->selectedSnapshot)
                    <div class="p-4 border rounded-lg bg-base-200 border-base-300">
                        <div class="text-sm font-semibold mb-2">{{ __('Restore Summary') }}</div>
                        <div class="text-sm opacity-70 space-y-1">
                            <div><strong>{{ __('Source:') }}</strong> {{ $this->selectedSourceServer?->name }} &bull; {{ $this->selectedSnapshot->database_name }}</div>
                            <div><strong>{{ __('Snapshot:') }}</strong> {{ \App\Support\Formatters::humanDate($this->selectedSnapshot->created_at) }}</div>
                            <div><strong>{{ __('Target:') }}</strong> {{ $targetServer?->name }} &bull; {{ $schemaName ?: __('(enter name)') }}</div>
                            <div><strong>{{ __('Size:') }}</strong> {{ $this->selectedSnapshot->getHumanFileSize() }}</div>
                        </div>
                    </div>
                @endif

                <div class="flex gap-2 mt-6">
                    <x-button class="btn-ghost" wire:click="previousStep">
                        {{ __('Back') }}
                    </x-button>
                    <div class="flex-1"></div>
                    <x-button class="btn-ghost" @click="$wire.showModal = false">
                        {{ __('Cancel') }}
                    </x-button>
                    <x-button class="btn-primary" wire:click="restore" spinner="restore">
                        {{ __('Restore Database') }}
                    </x-button>
                </div>
            </div>
        @endif

        <!-- Initial step buttons -->
        @if($currentStep === 1)
            <div class="flex gap-2 mt-6">
                <div class="flex-1"></div>
                <x-button class="btn-ghost" @click="$wire.showModal = false">
                    {{ __('Cancel') }}
                </x-button>
            </div>
        @endif
    </x-modal>
</div>

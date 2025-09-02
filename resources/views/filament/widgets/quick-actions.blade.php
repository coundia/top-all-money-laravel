<x-filament-widgets::widget>
    <x-filament::section>
        <div class="grid grid-cols-2 gap-4">
            <x-filament::button tag="a" href="{{ \App\Filament\Resources\Products\ProductResource::getUrl('create') }}">
                + New Product
            </x-filament::button>

            <x-filament::button tag="a" href="{{ \App\Filament\Resources\Customers\CustomerResource::getUrl('create') }}">
                + New Customer
            </x-filament::button>

            <x-filament::button tag="a" href="{{ \App\Filament\Resources\TransactionEntries\TransactionEntryResource::getUrl('create') }}">
                + New Transaction
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

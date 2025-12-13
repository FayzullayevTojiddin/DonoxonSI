<x-filament-panels::page>
    @livewire(\Filament\Widgets\AccountWidget::class)
    
    <div class="space-y-6">
        @foreach ($this->getWidgets() as $widget)
            @livewire($widget)
        @endforeach
    </div>
</x-filament-panels::page>
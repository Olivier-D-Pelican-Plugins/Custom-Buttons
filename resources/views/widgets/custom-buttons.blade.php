<x-filament-widgets::widget>
    @if(count($this->getActions()) > 0)
        <div class="flex gap-2 flex-wrap">
            @foreach($this->getActions() as $action)
                {{ $action }}
            @endforeach
        </div>
    @endif
</x-filament-widgets::widget>

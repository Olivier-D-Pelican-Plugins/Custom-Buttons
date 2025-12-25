<?php

namespace Olivier\CustomButtons\Filament\Server\Resources\CustomButtons\Pages;

use App\Traits\Filament\CanCustomizeHeaderActions;
use App\Traits\Filament\CanCustomizeHeaderWidgets;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Olivier\CustomButtons\Filament\Server\Resources\CustomButtons\CustomButtonResource;

class ListCustomButtons extends ListRecords
{
    use CanCustomizeHeaderActions;
    use CanCustomizeHeaderWidgets;

    protected static string $resource = CustomButtonResource::class;

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function getTitle(): string|Htmlable
    {
        return trans('Custom-Buttons::buttons.title');
    }
}

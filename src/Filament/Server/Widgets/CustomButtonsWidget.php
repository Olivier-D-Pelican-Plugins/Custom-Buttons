<?php

namespace Olivier\CustomButtons\Filament\Server\Widgets;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Support\Enums\Size;
use Filament\Widgets\Widget;
use Olivier\CustomButtons\Models\CustomButton;
use Olivier\CustomButtons\Services\UrlTemplateParser;

class CustomButtonsWidget extends Widget
{
    protected string $view = 'Custom-Buttons::widgets.custom-buttons';

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    public function getActions(): array
    {
        try {
            $server = Filament::getTenant();
            if (!$server) {
                return [];
            }
            
            return CustomButton::active()
                ->forServer($server->id)
                ->orderBy('sort')
                ->get()
                ->map(fn ($button) => Action::make("button_{$button->id}")
                    ->label($button->text)
                    ->icon($button->icon ?? 'tabler-link')
                    ->color($button->color)
                    ->url(UrlTemplateParser::parse($button->url, $server), $button->new_tab)
                    ->size(Size::ExtraLarge)
                )->all();
        } catch (\Exception $e) {
            return [];
        }
    }
}

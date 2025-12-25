<?php

namespace Olivier\CustomButtons\Filament\Components\Actions;

use Filament\Actions\Action;
use Filament\Support\Enums\Size;

class CustomButtonAction extends Action
{
    protected array $buttonData = [];

    public function buttonData(array $data): static
    {
        $this->buttonData = $data;
        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn () => $this->buttonData['text'] ?? 'Custom Button');

        $this->icon(fn () => $this->buttonData['icon'] ?? 'tabler-link');

        $this->color(fn () => $this->buttonData['color'] ?? 'primary');

        $this->size(Size::ExtraLarge);

        $this->url(fn () => $this->buttonData['url'] ?? '#');

        if ($this->buttonData['new_tab'] ?? true) {
            $this->openUrlInNewTab();
        }
    }
}

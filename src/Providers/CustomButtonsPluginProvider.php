<?php

namespace Olivier\CustomButtons\Providers;

use App\Enums\ConsoleWidgetPosition;
use App\Filament\Server\Pages\Console;
use App\Models\Subuser;
use Illuminate\Support\ServiceProvider;
use Olivier\CustomButtons\Filament\Server\Widgets\CustomButtonsWidget;

class CustomButtonsPluginProvider extends ServiceProvider
{
    public function register(): void
    {
        Subuser::registerCustomPermissions('custombuttons', [
            'view',
            'create',
            'edit',
            'delete',
        ], 'tabler-layout-grid-add', false);
        
        Console::registerCustomWidgets(
            ConsoleWidgetPosition::AboveConsole,
            [CustomButtonsWidget::class]
        );
    }

    public function boot(): void
    {
    }
}

<?php

namespace Olivier\CustomButtons;

use App\Contracts\Plugins\HasPluginSettings;
use App\Enums\HeaderActionPosition;
use App\Filament\Server\Pages\Console;
use Filament\Actions\Action;
use Filament\Contracts\Plugin;
use Filament\Notifications\Notification;
use Filament\Panel;
use Filament\Support\Enums\Size;
use Illuminate\Support\Facades\Schema;
use Olivier\CustomButtons\Models\CustomButton;
use Olivier\CustomButtons\Models\CustomSidebarItem;
use Olivier\CustomButtons\Services\FeatureChecker;
use Olivier\CustomButtons\Services\UrlTemplateParser;

use function Filament\Facades\Filament as FilamentFacade;

class CustomButtonsPlugin implements HasPluginSettings, Plugin
{

    public function getId(): string
    {
        return 'Custom-Buttons';
    }

    public function register(Panel $panel): void
    {
        if ($panel->getId() === 'server') {
            $panel->discoverResources(
                plugin_path($this->getId(), 'src/Filament/Server/Resources'),
                'Olivier\\CustomButtons\\Filament\\Server\\Resources'
            );
        }
    }

    public function boot(Panel $panel): void
    {
        if ($panel->getId() !== 'server') {
            return;
        }

        try {
            $server = \Filament\Facades\Filament::getTenant();
            
            // Sidebar items
            $items = [];
            foreach (CustomSidebarItem::active()->orderBy('sort')->get() as $item) {
                $itemFeature = $item->feature;
                $itemUrl = $item->url;
                $navItem = \Filament\Navigation\NavigationItem::make($item->label)
                    ->url(function () use ($itemUrl) {
                        $currentServer = \Filament\Facades\Filament::getTenant();
                        return $currentServer ? UrlTemplateParser::parse($itemUrl, $currentServer) : $itemUrl;
                    })
                    ->sort($item->sort)
                    ->visible(function () use ($itemFeature) {
                        if (!$itemFeature) {
                            return true;
                        }
                        $currentServer = \Filament\Facades\Filament::getTenant();
                        return $currentServer && FeatureChecker::hasFeature($currentServer, $itemFeature);
                    });

                if ($item->icon) {
                    $navItem->icon($item->icon);
                }

                if ($item->new_tab) {
                    $navItem->openUrlInNewTab();
                }

                $items[] = $navItem;
            }
            
            if ($items) {
                $panel->navigationItems($items);
            }
            
            // Global console buttons
            foreach (CustomButton::active()->global()->orderBy('sort')->get() as $button) {
                $buttonFeature = $button->feature;
                $buttonUrl = $button->url;
                $buttonNewTab = $button->new_tab;
                
                Console::registerCustomHeaderActions(
                    HeaderActionPosition::Before,
                    Action::make("global_button_{$button->id}")
                        ->label($button->text)
                        ->icon($button->icon ?? 'tabler-link')
                        ->color($button->color)
                        ->url(function () use ($buttonUrl) {
                            $currentServer = \Filament\Facades\Filament::getTenant();
                            return $currentServer ? UrlTemplateParser::parse($buttonUrl, $currentServer) : $buttonUrl;
                        }, $buttonNewTab)
                        ->size(Size::ExtraLarge)
                        ->visible(function () use ($buttonFeature) {
                            if (!$buttonFeature) {
                                return true;
                            }
                            $currentServer = \Filament\Facades\Filament::getTenant();
                            return $currentServer && FeatureChecker::hasFeature($currentServer, $buttonFeature);
                        })
                );
            }
        } catch (\Exception $e) {
        }
    }

    public function getSettingsForm(): array
    {
        return [
            \Filament\Forms\Components\Repeater::make('buttons')
                ->label('Console Buttons')
                ->schema([
                    \Filament\Forms\Components\TextInput::make('text')
                        ->label('Button Text')
                        ->required()
                        ->maxLength(255),
                    \Filament\Forms\Components\TextInput::make('url')
                        ->label('Button URL')
                        ->required()
                        ->maxLength(255),
                    \Filament\Forms\Components\TextInput::make('icon')
                        ->label('Icon (Tabler icon name)')
                        ->placeholder('tabler-link')
                        ->helperText('See https://tabler.io/icons')
                        ->maxLength(255),
                    \Filament\Forms\Components\Select::make('color')
                        ->label('Button Color')
                        ->options([
                            'primary' => 'Primary',
                            'success' => 'Success',
                            'warning' => 'Warning',
                            'danger' => 'Danger',
                            'info' => 'Info',
                            'gray' => 'Gray',
                        ])
                        ->default('primary'),
                    \Filament\Forms\Components\TextInput::make('sort')
                        ->label('Sort Order')
                        ->numeric()
                        ->default(0)
                        ->helperText('Lower numbers appear first'),
                    \Filament\Forms\Components\TextInput::make('feature')
                        ->label('Required Feature')
                        ->placeholder('e.g., eula, geyser')
                        ->helperText('Only show this button if server egg has this feature')
                        ->maxLength(255),
                    \Filament\Forms\Components\Toggle::make('new_tab')
                        ->label('Open in new tab')
                        ->default(true)
                        ->inline(false),
                    \Filament\Forms\Components\Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->inline(false),
                ])
                ->collapsible()
                ->defaultItems(0)
                ->addActionLabel('Add Button')
                ->columns(2)
                ->default(fn () => $this->loadButtons()),
            \Filament\Forms\Components\Repeater::make('sidebar_items')
                ->label('Sidebar Navigation Items')
                ->schema([
                    \Filament\Forms\Components\TextInput::make('label')
                        ->label('Label')
                        ->required()
                        ->maxLength(255),
                    \Filament\Forms\Components\TextInput::make('url')
                        ->label('URL')
                        ->required()
                        ->maxLength(255),
                    \Filament\Forms\Components\TextInput::make('icon')
                        ->label('Icon (Tabler icon name)')
                        ->placeholder('tabler-link')
                        ->helperText('See https://tabler.io/icons')
                        ->maxLength(255),
                    \Filament\Forms\Components\TextInput::make('sort')
                        ->label('Sort Order')
                        ->numeric()
                        ->default(50)
                        ->helperText('Lower numbers appear first'),
                    \Filament\Forms\Components\TextInput::make('feature')
                        ->label('Required Feature')
                        ->placeholder('e.g., eula, geyser')
                        ->helperText('Only show this item if server egg has this feature')
                        ->maxLength(255),
                    \Filament\Forms\Components\Toggle::make('new_tab')
                        ->label('Open in new tab')
                        ->default(false)
                        ->inline(false),
                    \Filament\Forms\Components\Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->inline(false),
                ])
                ->collapsible()
                ->defaultItems(0)
                ->addActionLabel('Add Navigation Item')
                ->columns(2)
                ->default(fn () => $this->loadSidebarItems()),
        ];
    }

    protected function loadButtons(): array
    {
        try {
            if (!Schema::hasTable('custom_buttons')) {
                return [];
            }
            
            return CustomButton::global()->get()->map(fn ($button) => [
                'text' => $button->text,
                'url' => $button->url,
                'icon' => $button->icon,
                'color' => $button->color,
                'new_tab' => $button->new_tab,
                'sort' => $button->sort,
                'is_active' => $button->is_active,
                'feature' => $button->feature,
            ])->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function loadSidebarItems(): array
    {
        try {
            if (!Schema::hasTable('custom_sidebar_items')) {
                return [];
            }
            
            return CustomSidebarItem::orderBy('sort')->get()->map(fn ($item) => [
                'label' => $item->label,
                'url' => $item->url,
                'icon' => $item->icon,
                'sort' => $item->sort,
                'new_tab' => $item->new_tab,
                'is_active' => $item->is_active,
                'feature' => $item->feature,
            ])->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function saveSettings(array $data): void
    {
        try {
            if (!Schema::hasTable('custom_buttons') || !Schema::hasTable('custom_sidebar_items')) {
                throw new \Exception('Required tables do not exist. Please run migrations.');
            }

            CustomButton::global()->delete();
            CustomSidebarItem::query()->delete();

            $allowedButtonFields = ['text', 'url', 'icon', 'color', 'new_tab', 'sort', 'is_active', 'feature'];
            $allowedItemFields = ['label', 'url', 'icon', 'sort', 'new_tab', 'is_active', 'feature'];

            foreach ($data['buttons'] ?? [] as $button) {
                $buttonData = array_intersect_key($button, array_flip($allowedButtonFields));
                
                if (empty($buttonData['text']) || empty($buttonData['url'])) {
                    continue;
                }
                
                CustomButton::create(array_merge([
                    'server_id' => null,
                    'color' => 'primary',
                    'sort' => 0,
                    'new_tab' => true,
                    'is_active' => true,
                ], $buttonData));
            }

            foreach ($data['sidebar_items'] ?? [] as $item) {
                $itemData = array_intersect_key($item, array_flip($allowedItemFields));
                
                if (empty($itemData['label']) || empty($itemData['url'])) {
                    continue;
                }
                
                CustomSidebarItem::create(array_merge([
                    'sort' => 50,
                    'new_tab' => false,
                    'is_active' => true,
                ], $itemData));
            }
            
            Notification::make()
                ->title('Settings saved')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error saving settings')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}

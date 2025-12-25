<?php

namespace Olivier\CustomButtons\Filament\Server\Resources\CustomButtons;

use App\Models\Server;
use App\Traits\Filament\CanCustomizePages;
use App\Traits\Filament\CanCustomizeRelations;
use App\Traits\Filament\CanModifyTable;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Olivier\CustomButtons\Filament\Server\Resources\CustomButtons\Pages\ListCustomButtons;
use Olivier\CustomButtons\Models\CustomButton;

class CustomButtonResource extends Resource
{
    use CanCustomizePages;
    use CanCustomizeRelations;
    use CanModifyTable;

    protected static ?string $model = CustomButton::class;

    protected static ?int $navigationSort = 99;

    protected static string|\BackedEnum|null $navigationIcon = 'tabler-layout-grid-add';

    public static function getNavigationLabel(): string
    {
        return trans('custombuttons::buttons.title');
    }

    public static function canAccess(): bool
    {
        /** @var Server $server */
        $server = Filament::getTenant();
        
        return user()?->can('custombuttons.view', $server) ?? false;
    }

    public static function table(Table $table): Table
    {
        return static::defaultTable($table);
    }

    public static function defaultTable(Table $table): Table
    {
        /** @var Server $server */
        $server = Filament::getTenant();

        return $table
            ->query(CustomButton::query()->forServer($server->id))
            ->columns([
                TextColumn::make('text')
                    ->label(trans('custombuttons::buttons.text'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('url')
                    ->label(trans('custombuttons::buttons.url'))
                    ->searchable()
                    ->limit(50),
                TextColumn::make('icon')
                    ->label(trans('custombuttons::buttons.icon'))
                    ->icon(fn (CustomButton $button) => $button->icon ?? 'tabler-link'),
                TextColumn::make('color')
                    ->label(trans('custombuttons::buttons.color'))
                    ->badge()
                    ->color(fn (CustomButton $button) => $button->color),
                IconColumn::make('new_tab')
                    ->label(trans('custombuttons::buttons.new_tab'))
                    ->boolean(),
                TextColumn::make('sort')
                    ->label(trans('custombuttons::buttons.sort'))
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(trans('custombuttons::buttons.is_active'))
                    ->boolean()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make()
                    ->label(trans('custombuttons::buttons.edit'))
                    ->authorize(fn () => user()?->can('custombuttons.edit', $server))
                    ->modalHeading(trans('custombuttons::buttons.edit_button'))
                    ->successNotificationTitle(null)
                    ->schema(static::getFormSchema())
                    ->action(function (array $data, CustomButton $record) {
                        $record->update($data);

                        Notification::make()
                            ->title(trans('custombuttons::buttons.notification_edit'))
                            ->success()
                            ->send();
                    }),
                DeleteAction::make()
                    ->label(trans('custombuttons::buttons.delete'))
                    ->authorize(fn () => user()?->can('custombuttons.delete', $server))
                    ->successNotificationTitle(null)
                    ->action(function (CustomButton $record) {
                        $record->delete();

                        Notification::make()
                            ->title(trans('custombuttons::buttons.notification_delete'))
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                CreateAction::make('create')
                    ->hiddenLabel()->iconButton()->iconSize(IconSize::ExtraLarge)
                    ->icon('tabler-plus')
                    ->tooltip(trans('custombuttons::buttons.create_button'))
                    ->createAnother(false)
                    ->authorize(fn () => user()?->can('custombuttons.create', $server))
                    ->modalHeading(trans('custombuttons::buttons.create_button'))
                    ->successNotificationTitle(null)
                    ->schema(static::getFormSchema())
                    ->action(function (array $data) use ($server) {
                        CustomButton::create([
                            ...$data,
                            'server_id' => $server->id,
                        ]);

                        Notification::make()
                            ->title(trans('custombuttons::buttons.notification_create'))
                            ->success()
                            ->send();
                    }),
            ]);
    }

    protected static function getFormSchema(): array
    {
        return [
            TextInput::make('text')
                ->label(trans('custombuttons::buttons.text'))
                ->required()
                ->maxLength(255),
            TextInput::make('url')
                ->label(trans('custombuttons::buttons.url'))
                ->required()
                ->maxLength(255),
            TextInput::make('icon')
                ->label(trans('custombuttons::buttons.icon'))
                ->placeholder('tabler-link')
                ->helperText(trans('custombuttons::buttons.icon_helper'))
                ->maxLength(255),
            Select::make('color')
                ->label(trans('custombuttons::buttons.color'))
                ->options([
                    'primary' => 'Primary',
                    'success' => 'Success',
                    'warning' => 'Warning',
                    'danger' => 'Danger',
                    'info' => 'Info',
                    'gray' => 'Gray',
                ])
                ->default('primary')
                ->required(),
            TextInput::make('sort')
                ->label(trans('custombuttons::buttons.sort'))
                ->numeric()
                ->default(0)
                ->helperText(trans('custombuttons::buttons.sort_helper'))
                ->required(),
            Toggle::make('new_tab')
                ->label(trans('custombuttons::buttons.new_tab'))
                ->default(true)
                ->inline(false),
            Toggle::make('is_active')
                ->label(trans('custombuttons::buttons.is_active'))
                ->default(true)
                ->inline(false),
        ];
    }

    /** @return array<string, PageRegistration> */
    public static function getDefaultPages(): array
    {
        return [
            'index' => ListCustomButtons::route('/'),
        ];
    }
}

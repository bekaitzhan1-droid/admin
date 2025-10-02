<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Driver;
use Illuminate\Database\QueryException;
use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Core\Exceptions\ResourceException;
use MoonShine\Laravel\Enums\Action;
use MoonShine\Support\Enums\PageType;
use MoonShine\Support\ListOf;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Text;

/**
 * @extends ModelResource<Driver>
 */
class DriverResource extends ModelResource
{
    protected string $model = Driver::class;

    protected string $title = 'Водители';

    protected bool $createInModal = true;

    protected ?PageType $redirectAfterSave = PageType::DETAIL;
    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('ИИН', 'iin')->sortable(),
            Text::make('ФИО', 'name')->sortable(),
            Text::make('Класс', 'class')->sortable(),
            // Text::make('Дата рождения', 'birth_date')->sortable(),
            Text::make('Возраст', 'age')->sortable(),
        ];
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {
        return [
            Box::make([
                ID::make(),
                Text::make('ИИН', 'iin')->required(),
            ])
        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function detailFields(): iterable
    {
        return [
            // ID::make(),
            Text::make('ИИН', 'iin'),
            Text::make('ФИО', 'name'),
            Text::make('Класс', 'class'),
            // Text::make('Дата рождения', 'birth_date'),
            Text::make('Возраст', 'age'),
            // Date::make('Создан', 'created_at')->format('d.m.Y H:i'),
            // Date::make('Обновлен', 'updated_at')->format('d.m.Y H:i'),
        ];
    }

    /**
     * @param Driver $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [
            'iin' => ['required', 'string', 'size:12'],
        ];
    }

    protected function search(): array
    {
        return ['iin', 'name'];
    }
}

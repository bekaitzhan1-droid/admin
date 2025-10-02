<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Confirmation;
use Illuminate\Contracts\Database\Eloquent\Builder;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Support\Enums\PageType;
use MoonShine\UI\Fields\Hidden;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\Laravel\Enums\Action;
use MoonShine\Support\ListOf;

/**
 * @extends ModelResource<Confirmation>
 */
class FreedomConfirmationResource extends ModelResource
{
    protected string $model = Confirmation::class;

    protected string $title = 'Freedom проверка';

    protected bool $createInModal = true;

    protected ?PageType $redirectAfterSave = PageType::DETAIL;

    protected function modifyQueryBuilder(Builder $builder): Builder
    {
        return $builder->where('type', 'freedom');
    }
    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('ИИН', 'iin'),
            Switcher::make('Подтвержден', 'is_confirmed'),
            Text::make('Текст', 'text'),
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
                Hidden::make('type')->default('freedom'),
                Text::make('ИИН', 'iin')->mask('999999999999')->required(),
            ])
        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function detailFields(): iterable
    {
        return [
            ID::make(),
            Text::make('ИИН', 'iin'),
            Switcher::make('Подтвержден', 'is_confirmed'),
            Text::make('Текст', 'text'),
        ];
    }

    /**
     * @param FreedomConfirmation $item
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
        return ['iin'];
    }

    protected function activeActions(): ListOf
    {
        return new ListOf(Action::class, [Action::CREATE, Action::VIEW, Action::DELETE]);
    }
}

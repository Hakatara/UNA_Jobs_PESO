<?php

namespace Botble\JobBoard\Repositories\Eloquent;

use Botble\Base\Models\BaseModel;
use Botble\JobBoard\Enums\CustomFieldEnum;
use Botble\JobBoard\Models\CustomFieldOption;
use Botble\JobBoard\Repositories\Interfaces\CustomFieldInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class CustomFieldRepository extends RepositoriesAbstract implements CustomFieldInterface
{
    public function createOrUpdate($data, array $condition = []): BaseModel|bool
    {
        /** @var BaseModel $data */
        if (is_array($data)) {
            if (empty($condition)) {
                $item = new $this->model();
            } else {
                $item = $this->getFirstBy($condition);
            }

            if (empty($item)) {
                $item = new $this->model();
            }

            $item = $item->fill($data);
        } elseif ($data instanceof Model) {
            $item = $data;
        } else {
            return false;
        }

        $this->resetModel();

        /** @var \Botble\JobBoard\Models\CustomField $item */
        $item->authorable()->associate(Auth::user() ?? Auth::guard('account')->user());

        if ($item->save()) {
            if (Arr::get($data, 'type') === CustomFieldEnum::TEXT) {
                Arr::forget($data, 'options');
            }

            $customFieldOptions = $this->formatOptions($data['options'] ?? []);

            $item->options()->whereNotIn('id', collect($customFieldOptions)->pluck('id')->all())->delete();

            if (count($customFieldOptions)) {
                $item->options()->saveMany($customFieldOptions);
            }

            return $item;
        }

        return false;
    }

    protected function formatOptions(array $options = []): array
    {
        $customFieldOptions = [];

        foreach ($options as $item) {
            $option = null;

            if (Arr::exists($item, 'id')) {
                $option = CustomFieldOption::query()->find($item['id']);
                $option->fill($item);
            }
            if (! $option) {
                $option = new CustomFieldOption($item);
            }
            $customFieldOptions[] = $option;
        }

        return $customFieldOptions;
    }
}

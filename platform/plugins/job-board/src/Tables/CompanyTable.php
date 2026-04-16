<?php

namespace Botble\JobBoard\Tables;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\JobBoard\Repositories\Interfaces\CompanyInterface;
use Botble\Media\Facades\RvMedia;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\DataTables;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CompanyTable extends TableAbstract
{
    protected $hasActions = true;

    protected $hasFilter = true;

    public function __construct(DataTables $table, UrlGenerator $urlGenerator, CompanyInterface $companyRepository)
    {
        parent::__construct($table, $urlGenerator);

        $this->repository = $companyRepository;

        if (! Auth::user()->hasAnyPermission(['companies.edit', 'companies.destroy'])) {
            $this->hasOperations = false;
            $this->hasActions = false;
        }
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function ($item) {
                if (! Auth::user()->hasPermission('companies.edit')) {
                    return $item->name;
                }

                return Html::link(route('companies.edit', $item->id), $item->name);
            })
            ->editColumn('logo', function ($item) {
                return Html::image(
                    RvMedia::getImageUrl($item->logo, 'thumb', false, RvMedia::getDefaultImage()),
                    $item->name,
                    ['width' => 50]
                );
            })
            ->editColumn('checkbox', function ($item) {
                return $this->getCheckbox($item->id);
            })
            ->editColumn('created_at', function ($item) {
                return BaseHelper::formatDate($item->created_at);
            })
            ->editColumn('status', function ($item) {
                return $item->status->toHtml();
            })
            ->addColumn('operations', function ($item) {
                $extra = Html::link(
                    route('companies.analytics', $item->id),
                    __('Analytics'),
                    ['class' => 'btn btn-info']
                )->toHtml();

                return $this->getOperations('companies.edit', 'companies.destroy', $item, $extra);
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->repository->getModel()->select([
            'id',
            'logo',
            'name',
            'created_at',
            'status',
        ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            'id' => [
                'name' => 'id',
                'title' => trans('core/base::tables.id'),
                'width' => '20px',
            ],
            'logo' => [
                'name' => 'logo',
                'title' => __('Logo'),
                'width' => '70px',
            ],
            'name' => [
                'name' => 'name',
                'title' => trans('core/base::tables.name'),
                'class' => 'text-start',
            ],
            'created_at' => [
                'name' => 'created_at',
                'title' => trans('core/base::tables.created_at'),
                'width' => '100px',
                'class' => 'text-start',
            ],
            'status' => [
                'name' => 'status',
                'title' => trans('core/base::tables.status'),
                'width' => '100px',
                'class' => 'text-start',
            ],
        ];
    }

    public function buttons(): array
    {
        $buttons = $this->addCreateButton(route('companies.create'), 'companies.create');

        if (Auth::user()->hasPermission('import-companies.index')) {
            $buttons['import'] = [
                'link' => route('import-companies.index'),
                'text' => '<i class="fas fa-cloud-upload-alt"></i> ' . trans('plugins/job-board::import.company.name'),
                'class' => 'btn-warning',
            ];
        }

        if (Auth::user()->hasPermission('export-companies.index')) {
            $buttons['export'] = [
                'link' => route('export-companies.index'),
                'text' => '<i class="fas fa-cloud-download-alt"></i> ' . trans('plugins/job-board::export.companies.name'),
                'class' => 'btn-warning',
            ];
        }

        return $buttons;
    }

    public function bulkActions(): array
    {
        return $this->addDeleteAction(route('companies.deletes'), 'companies.destroy', parent::bulkActions());
    }

    public function getBulkChanges(): array
    {
        return [
            'name' => [
                'title' => trans('core/base::tables.name'),
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'select',
                'choices' => BaseStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', BaseStatusEnum::values()),
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'datePicker',
            ],
            'is_completed_profile' => [
                'title' => __('Is completed profile?'),
                'type' => 'select',
                'choices' => [
                    'completed' => __('Yes'),
                    'incomplete' => __('No'),
                ],
            ],
        ];
    }

    public function getOperationsHeading(): array
    {
        return [
            'operations' => [
                'title' => trans('core/base::tables.operations'),
                'width' => '300px',
                'class' => 'text-center',
                'orderable' => false,
                'searchable' => false,
                'exportable' => false,
                'printable' => false,
            ],
        ];
    }

    public function applyFilterCondition(EloquentBuilder|QueryBuilder|EloquentRelation $query, string $key, string $operator, ?string $value): EloquentRelation|EloquentBuilder|QueryBuilder
    {
        if ($key == 'is_completed_profile') {
            switch ($value) {
                case 'completed':
                    // @phpstan-ignore-next-line
                    return $query->completedProfile();
                case 'incomplete':
                    // @phpstan-ignore-next-line
                    return $query->incompleteProfile();
            }
        }

        return parent::applyFilterCondition($query, $key, $operator, $value);
    }
}

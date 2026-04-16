<?php

namespace Botble\JobBoard\Tables;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\JobBoard\Repositories\Interfaces\DegreeLevelInterface;
use Botble\JobBoard\Repositories\Interfaces\DegreeTypeInterface;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\DataTables;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DegreeTypeTable extends TableAbstract
{
    protected $hasActions = true;

    protected $hasFilter = true;

    protected DegreeLevelInterface $degreeLevelRepository;

    public function __construct(
        DataTables $table,
        UrlGenerator $urlGenerator,
        DegreeTypeInterface $degreeTypeRepository,
        DegreeLevelInterface $degreeLevelRepository
    ) {
        parent::__construct($table, $urlGenerator);

        $this->repository = $degreeTypeRepository;
        $this->degreeLevelRepository = $degreeLevelRepository;

        if (! Auth::user()->hasAnyPermission(['degree-types.edit', 'degree-types.destroy'])) {
            $this->hasOperations = false;
            $this->hasActions = false;
        }
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function ($item) {
                if (! Auth::user()->hasPermission('degree-types.edit')) {
                    return $item->name;
                }

                return Html::link(route('degree-types.edit', $item->id), $item->name);
            })
            ->editColumn('degree_level_id', function ($item) {
                if (! $item->degree_level_id) {
                    return null;
                }

                return Html::link(route('degree-levels.edit', $item->degree_level_id), $item->level->name);
            })
            ->editColumn('checkbox', function ($item) {
                return $this->getCheckbox($item->id);
            })
            ->editColumn('is_default', function ($item) {
                return $item->is_default ? trans('core/base::base.yes') : trans('core/base::base.no');
            })
            ->editColumn('created_at', function ($item) {
                return BaseHelper::formatDate($item->created_at);
            })
            ->editColumn('status', function ($item) {
                return $item->status->toHtml();
            })
            ->addColumn('operations', function ($item) {
                return $this->getOperations('degree-types.edit', 'degree-types.destroy', $item);
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->repository->getModel()->select([
            'id',
            'name',
            'degree_level_id',
            'order',
            'is_default',
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
            'name' => [
                'name' => 'name',
                'title' => trans('core/base::tables.name'),
                'class' => 'text-start',
            ],
            'degree_level_id' => [
                'name' => 'degree_level_id',
                'title' => trans('plugins/job-board::degree-type.degree-level'),
                'class' => 'text-start',
            ],
            'order' => [
                'name' => 'order',
                'title' => trans('core/base::tables.order'),
                'width' => '100px',
            ],
            'is_default' => [
                'name' => 'order',
                'title' => trans('core/base::base.is_default'),
                'width' => '100px',
            ],
            'created_at' => [
                'name' => 'created_at',
                'title' => trans('core/base::tables.created_at'),
                'width' => '100px',
            ],
            'status' => [
                'name' => 'status',
                'title' => trans('core/base::tables.status'),
                'width' => '100px',
            ],
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('degree-types.create'), 'degree-types.create');
    }

    public function bulkActions(): array
    {
        return $this->addDeleteAction(route('degree-types.deletes'), 'degree-types.destroy', parent::bulkActions());
    }

    public function getBulkChanges(): array
    {
        return [
            'name' => [
                'title' => trans('core/base::tables.name'),
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'degree_level_id' => [
                'title' => trans('plugins/job-board::degree-type.degree-level'),
                'type' => 'select',
                'validate' => 'required|max:120',
                'callback' => 'getDegreeLevels',
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
        ];
    }

    public function getDegreeLevels(): array
    {
        return $this->degreeLevelRepository->pluck('jb_degree_levels.name', 'jb_degree_levels.id');
    }
}

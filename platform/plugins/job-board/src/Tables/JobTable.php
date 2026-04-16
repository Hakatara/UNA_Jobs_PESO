<?php

namespace Botble\JobBoard\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\JobBoard\Enums\JobStatusEnum;
use Botble\JobBoard\Enums\ModerationStatusEnum;
use Botble\JobBoard\Repositories\Interfaces\JobInterface;
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

class JobTable extends TableAbstract
{
    protected $hasActions = true;

    protected $hasFilter = true;

    public function __construct(DataTables $table, UrlGenerator $urlGenerator, JobInterface $jobRepository)
    {
        parent::__construct($table, $urlGenerator);

        $this->repository = $jobRepository;

        if (! Auth::user()->hasAnyPermission(['jobs.edit', 'jobs.destroy'])) {
            $this->hasOperations = false;
            $this->hasActions = false;
        }
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function ($item) {
                if (! Auth::user()->hasPermission('jobs.edit')) {
                    return $item->name;
                }

                return Html::link(route('jobs.edit', $item->id), $item->name);
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
            ->editColumn('moderation_status', function ($item) {
                return $item->moderation_status->toHtml();
            })
            ->addColumn('operations', function ($item) {
                $extra = Html::link(
                    route('jobs.analytics', $item->id),
                    __('Analytics'),
                    ['class' => 'btn btn-info']
                )->toHtml();

                return $this->getOperations('jobs.edit', 'jobs.destroy', $item, $extra);
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->repository->getModel()->select([
            'id',
            'name',
            'created_at',
            'status',
            'moderation_status',
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
            'moderation_status' => [
                'name' => 'moderation_status',
                'title' => trans('plugins/job-board::job.moderation_status'),
                'width' => '150px',
            ],
        ];
    }

    public function buttons(): array
    {
        $buttons = $this->addCreateButton(route('jobs.create'), 'jobs.create');

        if (Auth::user()->hasPermission('import-jobs.index')) {
            $buttons['import'] = [
                'link' => route('import-jobs.index'),
                'text' => '<i class="fas fa-cloud-upload-alt"></i> ' . trans('plugins/job-board::import.name'),
                'class' => 'btn-warning',
            ];
        }

        if (Auth::user()->hasPermission('export-jobs.index')) {
            $buttons['export'] = [
                'link' => route('export-jobs.index'),
                'text' => '<i class="fas fa-cloud-download-alt"></i> ' . trans('plugins/job-board::export.jobs.name'),
                'class' => 'btn-warning',
            ];
        }

        return $buttons;
    }

    public function bulkActions(): array
    {
        return $this->addDeleteAction(route('jobs.deletes'), 'jobs.destroy', parent::bulkActions());
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
                'choices' => JobStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', JobStatusEnum::values()),
            ],
            'moderation_status' => [
                'title' => trans('plugins/job-board::job.moderation_status'),
                'type' => 'select',
                'choices' => ModerationStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', ModerationStatusEnum::values()),
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'datePicker',
            ],
            'type' => [
                'title' => __('Type'),
                'type' => 'select',
                'choices' => [
                    'expired' => __('Expired Jobs'),
                    'without-company' => __('Jobs without a company'),
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
        if ($key == 'type') {
            switch ($value) {
                case 'expired':
                    // @phpstan-ignore-next-line
                    return $query->expired();
                case 'without-company':
                    return $query->whereNull('company_id');
            }
        }

        return parent::applyFilterCondition($query, $key, $operator, $value);
    }
}

<?php

namespace Botble\JobBoard\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\JobBoard\Enums\JobApplicationStatusEnum;
use Botble\JobBoard\Models\JobApplication;
use Botble\JobBoard\Repositories\Interfaces\JobApplicationInterface;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\DataTables;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class JobApplicationTable extends TableAbstract
{
    protected $hasActions = true;

    protected $hasFilter = true;

    public function __construct(
        DataTables $table,
        UrlGenerator $urlGenerator,
        JobApplicationInterface $jobApplicationRepository
    ) {
        parent::__construct($table, $urlGenerator);

        $this->repository = $jobApplicationRepository;
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('job_id', function (JobApplication $item) {
                if (! $item->job->name) {
                    return '&mdash;';
                }

                return Html::link(
                    $item->job->url,
                    $item->job->name . ' ' . Html::tag('i', '', ['class' => 'fas fa-external-link-alt']),
                    ['target' => '_blank'],
                    null,
                    false
                );
            })
            ->editColumn('phone', function (JobApplication $item) {
                return $item->phone ?: '&mdash;';
            })
            ->editColumn('is_external_apply', function (JobApplication $item) {
                return $item->is_external_apply ? __('External') : __('Internal');
            })
            ->editColumn('checkbox', function (JobApplication $item) {
                return $this->getCheckbox($item->id);
            })
            ->editColumn('created_at', function (JobApplication $item) {
                return BaseHelper::formatDate($item->created_at);
            })
            ->editColumn('status', function (JobApplication $item) {
                return $item->status->toHtml();
            })
            ->addColumn('operations', function (JobApplication $item) {
                return $this->getOperations('job-applications.edit', 'job-applications.destroy', $item);
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->repository->getModel()
            ->select([
                'id',
                'job_id',
                'email',
                'phone',
                'created_at',
                'is_external_apply',
                'status',
            ])
            ->with(['job', 'job.slugable']);

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
            'email' => [
                'name' => 'email',
                'title' => trans('plugins/job-board::job-application.tables.email'),
                'class' => 'text-start',
            ],
            'phone' => [
                'name' => 'phone',
                'title' => trans('plugins/job-board::job-application.tables.phone'),
                'class' => 'text-start',
            ],
            'job_id' => [
                'name' => 'job_id',
                'title' => __('Job Name'),
                'class' => 'text-start',
            ],
            'is_external_apply' => [
                'name' => 'is_external_apply',
                'title' => __('Type'),
                'class' => 'text-center',
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
            ],
        ];
    }

    public function bulkActions(): array
    {
        return $this->addDeleteAction(
            route('job-applications.deletes'),
            'job-applications.destroy',
            parent::bulkActions()
        );
    }

    public function getBulkChanges(): array
    {
        return [
            'first_name' => [
                'title' => __('First name'),
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'last_name' => [
                'title' => __('Last name'),
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'email' => [
                'title' => trans('core/base::tables.email'),
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'customSelect',
                'choices' => JobApplicationStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', JobApplicationStatusEnum::values()),
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'datePicker',
            ],
        ];
    }

    public function getFilters(): array
    {
        return [
            'first_name' => [
                'title' => __('First name'),
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'last_name' => [
                'title' => __('Last name'),
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'email' => [
                'title' => trans('core/base::tables.email'),
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'datePicker',
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'select',
                'choices' => JobApplicationStatusEnum::labels(),
                'validate' => 'required|' . Rule::in(JobApplicationStatusEnum::values()),
            ],
            'is_external_apply' => [
                'title' => __('Type'),
                'type' => 'select',
                'choices' => [0 => __('Internal'), 1 => __('External')],
            ],
        ];
    }

    public function getDefaultButtons(): array
    {
        return [
            'export',
            'reload',
        ];
    }
}

<?php

namespace Botble\JobBoard\Tables\Fronts;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\JobBoard\Enums\JobApplicationStatusEnum;
use Botble\JobBoard\Facades\JobBoardHelper;
use Botble\JobBoard\Models\Account;
use Botble\JobBoard\Repositories\Interfaces\JobApplicationInterface;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\DataTables;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class ApplicantTable extends TableAbstract
{
    protected $hasCheckbox = false;

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
            ->editColumn('job_id', function ($item) {
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
            ->editColumn('is_external_apply', function ($item) {
                return $item->is_external_apply ? __('External') : __('Internal');
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
            ->editColumn('company', function ($item) {
                return $item->job->company->name ?: '&mdash;';
            })
            ->addColumn('operations', function ($item) {
                return view(JobBoardHelper::viewPath('dashboard.table.actions'), [
                    'edit' => 'public.account.applicants.edit',
                    'item' => $item,
                ])->render();
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        /**
         * @var Account $account
         */
        $account = auth('account')->user();

        $query = $this->repository
            ->getModel()
            ->select([
                'id',
                'job_id',
                'email',
                'created_at',
                'is_external_apply',
                'status',
            ])
            ->whereHas('job.company.accounts', function (Builder $query) use ($account) {
                $query->where('account_id', $account->getKey());
            })
            ->with([
                'job:id,name,company_id',
                'job.slugable',
                'job.company:id,name',
            ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            'id' => [
                'title' => trans('core/base::tables.id'),
                'width' => '20px',
            ],
            'email' => [
                'title' => trans('plugins/job-board::job-application.tables.email'),
                'class' => 'text-start',
            ],
            'job_id' => [
                'title' => __('Job Name'),
                'class' => 'text-start',
            ],
            'is_external_apply' => [
                'title' => __('Type'),
                'class' => 'text-center',
            ],
            'company' => [
                'title' => __('Company'),
                'class' => 'text-center',
                'searchable' => false,
                'orderable' => false,
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'width' => '100px',
                'class' => 'text-start',
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
                'width' => '100px',
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

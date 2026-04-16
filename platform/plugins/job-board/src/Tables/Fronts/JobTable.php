<?php

namespace Botble\JobBoard\Tables\Fronts;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\JobBoard\Facades\JobBoardHelper;
use Botble\JobBoard\Models\Account;
use Botble\JobBoard\Repositories\Interfaces\JobInterface;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\DataTables;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class JobTable extends TableAbstract
{
    protected $hasCheckbox = false;

    protected Authenticatable|null|Account $account;

    public function __construct(DataTables $table, UrlGenerator $urlGenerator, JobInterface $jobRepository)
    {
        parent::__construct($table, $urlGenerator);
        $this->repository = $jobRepository;
        $this->account = auth('account')->user();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function ($item) {
                return Html::link(route('public.account.jobs.edit', $item->id), $item->name);
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
            ->editColumn('expire_date', function ($item) {
                if ($item->expire_date->isPast()) {
                    return Html::tag('span', $item->expire_date->toDateString(), ['class' => 'text-danger'])->toHtml();
                }

                if (Carbon::now()->diffInDays($item->expire_date) < 3) {
                    return Html::tag('span', $item->expire_date->toDateString(), ['class' => 'text-warning'])->toHtml();
                }

                return $item->expire_date->toDateString();
            })
            ->addColumn('operations', function ($item) {
                $extra = Html::link(
                    route('public.account.jobs.analytics', $item->id),
                    Html::tag('i', '', ['class' => 'fas fa-chart-bar']),
                    ['class' => 'btn btn-secondary btn-sm'],
                    null,
                    false,
                )->toHtml();

                return view(JobBoardHelper::viewPath('dashboard.table.actions'), [
                    'edit' => 'public.account.jobs.edit',
                    'delete' => 'public.account.jobs.destroy',
                    'item' => $item,
                    'extra' => $extra,
                ])->render();
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->repository->getModel()
            ->select([
                'id',
                'name',
                'created_at',
                'status',
                'moderation_status',
                'expire_date',
            ])
            ->byAccount($this->account->getKey());

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
            'expire_date' => [
                'name' => 'expire_date',
                'title' => __('Expire date'),
                'width' => '150px',
            ],
        ];
    }

    public function buttons(): array
    {
        $buttons = [];

        // @phpstan-ignore-next-line
        if ($this->account->canPost()) {
            $buttons = $this->addCreateButton(route('public.account.jobs.create'));
        }

        return $buttons;
    }

    public function bulkActions(): array
    {
        return $this->addDeleteAction(route('public.account.jobs.deletes'), null, parent::bulkActions());
    }
}

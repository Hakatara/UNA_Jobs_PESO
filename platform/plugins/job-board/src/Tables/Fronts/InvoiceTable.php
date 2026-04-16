<?php

namespace Botble\JobBoard\Tables\Fronts;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\JobBoard\Facades\JobBoardHelper;
use Botble\JobBoard\Models\Account;
use Botble\JobBoard\Repositories\Interfaces\InvoiceInterface;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\DataTables;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class InvoiceTable extends TableAbstract
{
    protected $hasCheckbox = false;

    protected Authenticatable|null|Account $account;

    public function __construct(DataTables $table, UrlGenerator $urlGenerator, InvoiceInterface $invoiceRepository)
    {
        parent::__construct($table, $urlGenerator);

        $this->repository = $invoiceRepository;

        $this->account = auth('account')->user();
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('checkbox', function ($item) {
                return $this->getCheckbox($item->id);
            })
            ->editColumn('code', function ($item) {
                return $item->code;
            })
            ->editColumn('amount', function ($item) {
                return format_price($item->amount);
            })
            ->editColumn('created_at', function ($item) {
                return BaseHelper::formatDate($item->created_at);
            })
            ->editColumn('status', function ($item) {
                return $item->status->toHtml();
            })
            ->addColumn('operations', function ($item) {
                $extra = Html::link(
                    route('public.account.invoices.show', $item->id),
                    Html::tag('i', '', ['class' => 'fas fa-eye']),
                    ['class' => 'btn btn-primary btn-sm'],
                    null,
                    false,
                )->toHtml();

                return view(JobBoardHelper::viewPath('dashboard.table.actions'), [
                    'item' => $item,
                    'extra' => $extra,
                ])->render();
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $account = $this->account;

        $query = $this->repository->getModel()
            ->select([
                'id',
                'code',
                'amount',
                'status',
                'created_at',
            ])
            ->whereHas('payment', function (Builder $query) use ($account) {
                $query->where('customer_id', $account->getKey());
            });

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            'id' => [
                'name' => trans('core/base::tables.id'),
                'width' => '20px',
            ],
            'code' => [
                'title' => trans('plugins/job-board::invoice.table.code'),
                'class' => 'text-start',
            ],
            'amount' => [
                'title' => trans('plugins/job-board::invoice.table.amount'),
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

    public function getDefaultButtons(): array
    {
        return [
            'export',
            'reload',
        ];
    }
}

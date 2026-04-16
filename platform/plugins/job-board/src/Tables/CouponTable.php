<?php

namespace Botble\JobBoard\Tables;

use Botble\Base\Facades\Html;
use Botble\JobBoard\Enums\CouponTypeEnum;
use Botble\JobBoard\Models\Coupon;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\DataTables;
use Carbon\Carbon;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CouponTable extends TableAbstract
{
    public function __construct(DataTables $table, UrlGenerator $urlGenerator, Coupon $model)
    {
        parent::__construct($table, $urlGenerator);

        $this->model = $model;
        $this->hasActions = true;
        $this->hasFilter = true;

        if (! Auth::user()->hasPermission('coupons.destroy')) {
            $this->hasOperations = false;
            $this->hasActions = false;
        }
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('checkbox', function (Coupon $coupon) {
                return $this->getCheckbox($coupon->getKey());
            })
            ->editColumn('code', function (Coupon $coupon) {
                $value = $coupon->type == CouponTypeEnum::PERCENTAGE()->getValue()
                    ? number_format($coupon->value) . '%'
                    : format_price($coupon->value);

                return view(
                    'plugins/job-board::coupons.partials.detail',
                    compact('coupon', 'value')
                )->render();
            })
            ->editColumn('expires_date', function (Coupon $coupon) {
                if (! $coupon->expires_date) {
                    return '&mdash;';
                }

                return $coupon->expires_date;
            })
            ->editColumn('status', function (Coupon $coupon) {
                if ($coupon->expires_date !== null && Carbon::now()->gt($coupon->expires_date)) {
                    return Html::tag('span', trans('plugins/job-board::coupon.expired'), [
                        'class' => 'status-label label-default',
                    ]);
                }

                return Html::tag('span', trans('plugins/job-board::coupon.active'), [
                    'class' => 'status-label label-success',
                ]);
            })
            ->addColumn('operations', function (Coupon $coupon) {
                return $this->getOperations('coupons.edit', 'coupons.destroy', $coupon);
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->getModel()->query()->select(['*']);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            'id' => [
                'title' => trans('core/base::tables.id'),
                'width' => '20px',
                'class' => 'text-start',
            ],
            'code' => [
                'name' => 'code',
                'title' => trans('plugins/job-board::coupon.coupon_code'),
                'class' => 'text-start',
            ],
            'total_used' => [
                'name' => 'total_used',
                'title' => trans('plugins/job-board::coupon.total_used'),
                'class' => 'text-start',
            ],
            'expires_date' => [
                'title' => trans('plugins/job-board::coupon.expires_date'),
                'class' => 'text-start',
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
                'width' => '100px',
                'sortable' => false,
                'searchable' => false,
            ],
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('coupons.create'), 'coupons.create');
    }

    public function bulkActions(): array
    {
        return $this->addDeleteAction(route('coupons.deletes'), 'coupons.destroy', parent::bulkActions());
    }

    public function renderTable($data = [], $mergeData = []): View|Factory|Response
    {
        if (
            ! $this->query()->exists()
            && ! $this->request()->wantsJson()
            && $this->request()->input('filter_table_id') !== $this->getOption('id')
            && ! $this->request()->ajax()
        ) {
            return view('plugins/job-board::coupons.intro');
        }

        return parent::renderTable($data, $mergeData);
    }
}

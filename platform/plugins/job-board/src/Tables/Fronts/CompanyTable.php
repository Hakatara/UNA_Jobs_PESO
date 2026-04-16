<?php

namespace Botble\JobBoard\Tables\Fronts;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\JobBoard\Facades\JobBoardHelper;
use Botble\JobBoard\Models\Account;
use Botble\JobBoard\Repositories\Interfaces\CompanyInterface;
use Botble\Media\Facades\RvMedia;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\DataTables;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class CompanyTable extends TableAbstract
{
    protected $hasCheckbox = false;

    public function __construct(DataTables $table, UrlGenerator $urlGenerator, CompanyInterface $companyRepository)
    {
        parent::__construct($table, $urlGenerator);

        $this->repository = $companyRepository;
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function ($item) {
                return Html::link(route('public.account.companies.edit', $item->id), $item->name);
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
                return view(JobBoardHelper::viewPath('dashboard.table.actions'), [
                    'edit' => 'public.account.companies.edit',
                    'delete' => 'public.account.companies.destroy',
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

        $query = $this->repository->getModel()
            ->select([
                'id',
                'logo',
                'name',
                'created_at',
                'status',
            ])
            ->whereHas('accounts', function ($query) use ($account) {
                $query->where('account_id', $account->getKey());
            })
            ->withCount(['jobs' => function ($query) {
                $query->where('status', BaseStatusEnum::PUBLISHED);
            }]);

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
        return $this->addCreateButton(route('public.account.companies.create'));
    }

    public function bulkActions(): array
    {
        return $this->addDeleteAction(route('public.account.companies.deletes'), null, parent::bulkActions());
    }

    public function getBulkChanges(): array
    {
        return [
            'name' => [
                'title' => trans('core/base::tables.name'),
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
        ];
    }
}

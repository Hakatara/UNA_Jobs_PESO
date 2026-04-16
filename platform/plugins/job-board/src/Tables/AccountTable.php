<?php

namespace Botble\JobBoard\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\JobBoard\Enums\AccountTypeEnum;
use Botble\JobBoard\Models\Account;
use Botble\JobBoard\Repositories\Interfaces\AccountInterface;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\DataTables;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AccountTable extends TableAbstract
{
    protected $hasActions = true;

    protected $hasFilter = true;

    public function __construct(DataTables $table, UrlGenerator $urlGenerator, AccountInterface $accountRepository)
    {
        parent::__construct($table, $urlGenerator);

        $this->repository = $accountRepository;

        if (! Auth::user()->hasAnyPermission(['accounts.edit', 'accounts.destroy'])) {
            $this->hasOperations = false;
            $this->hasActions = false;
        }
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('first_name', function (Account $item) {
                if (! Auth::user()->hasPermission('accounts.edit')) {
                    return $item->name;
                }

                return Html::link(route('accounts.edit', $item->id), $item->name);
            })
            ->editColumn('checkbox', function (Account $item) {
                return $this->getCheckbox($item->id);
            })
            ->editColumn('phone', function (Account $item) {
                return $item->phone ?: '&mdash;';
            })
            ->editColumn('created_at', function (Account $item) {
                return BaseHelper::formatDate($item->created_at);
            })
            ->editColumn('type', function (Account $item) {
                return $item->type->toHtml();
            })
            ->editColumn('updated_at', function (Account $item) {
                $html = '';
                foreach ($item->companies as $key => $company) {
                    if (! $company->id) {
                        continue;
                    }

                    $html .= Html::link(route('companies.edit', $company->id), $company->name);
                    if ($key < count($item->companies) - 1) {
                        $html .= ', ';
                    }
                }

                return $html ?: '&mdash;';
            })
            ->addColumn('operations', function (Account $item) {
                return $this->getOperations('accounts.edit', 'accounts.destroy', $item);
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->repository->getModel()
            ->select([
                'id',
                'first_name',
                'last_name',
                'email',
                'phone',
                'type',
                'updated_at',
                'created_at',
            ])
            ->with('companies');

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
            'first_name' => [
                'name' => 'first_name',
                'title' => trans('core/base::tables.name'),
                'class' => 'text-start',
            ],
            'email' => [
                'name' => 'email',
                'title' => trans('core/base::tables.email'),
                'class' => 'text-start',
            ],
            'phone' => [
                'name' => 'phone',
                'title' => trans('plugins/job-board::job-application.tables.phone'),
                'class' => 'text-start',
            ],
            'type' => [
                'name' => 'type',
                'title' => trans('plugins/job-board::account.type'),
                'class' => 'text-start',
            ],
            'updated_at' => [
                'name' => 'updated_at',
                'title' => __('Company'),
                'class' => 'text-start',
            ],
            'created_at' => [
                'name' => 'created_at',
                'title' => trans('core/base::tables.created_at'),
                'width' => '100px',
            ],
        ];
    }

    public function buttons(): array
    {
        $buttons = $this->addCreateButton(route('accounts.create'), 'accounts.create');

        if (Auth::user()->hasPermission('accounts.import')) {
            $buttons['import'] = [
                'link' => route('accounts.import'),
                'text' => '<i class="fas fa-cloud-upload-alt"></i> ' . trans('plugins/job-board::account.import.name'),
                'class' => 'btn-warning',
            ];
        }

        if (Auth::user()->hasPermission('accounts.export')) {
            $buttons['export'] = [
                'link' => route('accounts.export'),
                'text' => '<i class="fas fa-cloud-download-alt"></i> ' . trans('plugins/job-board::account.export.name'),
                'class' => 'btn-warning',
            ];
        }

        return $buttons;
    }

    public function bulkActions(): array
    {
        return $this->addDeleteAction(route('accounts.deletes'), 'accounts.destroy', parent::bulkActions());
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
                'validate' => 'required|max:120|email',
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'datePicker',
            ],
            'type' => [
                'title' => __('Type'),
                'type' => 'select',
                'choices' => AccountTypeEnum::labels(),
                'validate' => 'required|in:' . implode(',', AccountTypeEnum::values()),
            ],
        ];
    }
}

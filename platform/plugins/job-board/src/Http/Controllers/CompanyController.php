<?php

namespace Botble\JobBoard\Http\Controllers;

use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\JobBoard\Forms\CompanyForm;
use Botble\JobBoard\Http\Requests\AjaxCompanyRequest;
use Botble\JobBoard\Http\Requests\CompanyRequest;
use Botble\JobBoard\Http\Resources\CompanyResource;
use Botble\JobBoard\Repositories\Interfaces\CompanyInterface;
use Botble\JobBoard\Services\StoreCompanyAccountService;
use Botble\JobBoard\Tables\CompanyTable;
use Exception;
use Illuminate\Http\Request;

class CompanyController extends BaseController
{
    public function __construct(protected CompanyInterface $companyRepository)
    {
    }

    public function index(CompanyTable $table)
    {
        PageTitle::setTitle(trans('plugins/job-board::company.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/job-board::company.create'));

        return $formBuilder->create(CompanyForm::class)->renderForm();
    }

    public function store(
        CompanyRequest $request,
        BaseHttpResponse $response,
        StoreCompanyAccountService $storeCompanyAccountService
    ) {
        $company = $this->companyRepository->createOrUpdate($request->input());

        event(new CreatedContentEvent(COMPANY_MODULE_SCREEN_NAME, $request, $company));

        $storeCompanyAccountService->execute($request, $company);

        return $response
            ->setPreviousUrl(route('companies.index'))
            ->setNextUrl(route('companies.edit', $company->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(int|string $id, FormBuilder $formBuilder, Request $request)
    {
        $company = $this->companyRepository->findOrFail($id);

        event(new BeforeEditContentEvent($request, $company));

        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $company->name]));

        return $formBuilder->create(CompanyForm::class, ['model' => $company])->renderForm();
    }

    public function update(
        $id,
        CompanyRequest $request,
        BaseHttpResponse $response,
        StoreCompanyAccountService $storeCompanyAccountService
    ) {
        $company = $this->companyRepository->findOrFail($id);

        $company->fill($request->input());

        $this->companyRepository->createOrUpdate($company);

        $storeCompanyAccountService->execute($request, $company);

        event(new UpdatedContentEvent(COMPANY_MODULE_SCREEN_NAME, $request, $company));

        return $response
            ->setPreviousUrl(route('companies.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(int|string $id, Request $request, BaseHttpResponse $response)
    {
        try {
            $company = $this->companyRepository->findOrFail($id);

            $this->companyRepository->delete($company);

            event(new DeletedContentEvent(COMPANY_MODULE_SCREEN_NAME, $request, $company));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function deletes(Request $request, BaseHttpResponse $response)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return $response
                ->setError()
                ->setMessage(trans('core/base::notices.no_select'));
        }

        foreach ($ids as $id) {
            $company = $this->companyRepository->findOrFail($id);
            $this->companyRepository->delete($company);
            event(new DeletedContentEvent(COMPANY_MODULE_SCREEN_NAME, $request, $company));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }

    public function getList(Request $request, BaseHttpResponse $response)
    {
        $keyword = $request->input('q');

        if (! $keyword) {
            return $response->setData([]);
        }

        $data = $this->companyRepository->advancedGet([
            'condition' => [
                ['jb_companies.name', 'LIKE', '%' . $keyword . '%'],
            ],
            'select' => ['jb_companies.id', 'jb_companies.name'],
            'take' => 10,

        ]);

        return $response->setData(CompanyResource::collection($data));
    }

    public function ajaxGetCompany(int $id, BaseHttpResponse $response)
    {
        $company = $this->companyRepository->findOrFail($id);

        return $response->setData(new CompanyResource($company));
    }

    public function ajaxCreateCompany(AjaxCompanyRequest $request, BaseHttpResponse $response)
    {
        $company = $this->companyRepository->createOrUpdate($request->input());

        event(new CreatedContentEvent(COMPANY_MODULE_SCREEN_NAME, $request, $company));

        return $response->setData(new CompanyResource($company));
    }

    public function getAllCompanies()
    {
        return $this->companyRepository->pluck('name');
    }

    public function analytics(int $id)
    {
        $company = $this->companyRepository->getModel()
            ->where('id', $id)
            ->withCount(['jobs'])
            ->firstOrFail();

        Assets::addScripts(['counterup', 'equal-height'])
            ->addStylesDirectly('vendor/core/core/dashboard/css/dashboard.css');

        PageTitle::setTitle(__('Analytics for company ":name"', ['name' => $company->name]));

        return view('plugins/job-board::company.analytics', compact('company'));
    }
}

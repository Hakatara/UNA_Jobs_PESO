<?php

namespace Botble\JobBoard\Http\Controllers\Fronts;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\EmailHandler;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\JobBoard\Facades\JobBoardHelper;
use Botble\JobBoard\Forms\Fronts\CompanyForm;
use Botble\JobBoard\Http\Requests\AccountCompanyRequest;
use Botble\JobBoard\Models\Account;
use Botble\JobBoard\Models\Company;
use Botble\JobBoard\Repositories\Interfaces\AccountActivityLogInterface;
use Botble\JobBoard\Repositories\Interfaces\AccountInterface;
use Botble\JobBoard\Repositories\Interfaces\CompanyInterface;
use Botble\JobBoard\Tables\Fronts\CompanyTable;
use Botble\Media\Facades\RvMedia;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Slug\Services\SlugService;
use Botble\Theme\Facades\Theme;
use Exception;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CompanyController extends Controller
{
    public function __construct(
        Repository $config,
        protected AccountInterface $accountRepository,
        protected CompanyInterface $companyRepository,
        protected AccountActivityLogInterface $activityLogRepository
    ) {
        Assets::setConfig($config->get('plugins.job-board.assets'));
    }

    public function index(CompanyTable $table)
    {
        PageTitle::setTitle(__('Companies'));

        SeoHelper::setTitle(__('Companies'));

        Theme::breadcrumb()
            ->add(__('Home'), route('public.index'))
            ->add(__('My Profile'), route('public.account.dashboard'))
            ->add(__('Companies'));

        return $table->render(JobBoardHelper::viewPath('dashboard.table.base'));
    }

    public function create(FormBuilder $formBuilder)
    {
        SeoHelper::setTitle(__('Create a company'));
        Theme::breadcrumb()
            ->add(__('Home'), route('public.index'))
            ->add(__('My Profile'), route('public.account.dashboard'))
            ->add(__('Companies'), route('public.account.companies.index'))
            ->add(__('Create a company'));

        PageTitle::setTitle(__('Create a company'));

        Assets::addStyles(['datetimepicker'])
            ->addScripts([
                'moment',
                'datetimepicker',
                'jquery-ui',
                'input-mask',
                'blockui',
            ]);

        return $formBuilder->create(CompanyForm::class)->renderForm();
    }

    public function store(AccountCompanyRequest $request, BaseHttpResponse $response)
    {
        /**
         * @var Account $account
         */
        $account = auth('account')->user();

        $request->merge([
            'status' => setting('verify_account_created_company', 1) == 1 ? BaseStatusEnum::PENDING : BaseStatusEnum::PUBLISHED,
            'logo' => null,
            'cover_image' => null,
            'is_featured' => false,
        ]);

        $request = $this->handleUpload($request);

        $company = $this->companyRepository->createOrUpdate($request->input());

        $company->accounts()->syncWithoutDetaching(['account_id' => $account->getKey()]);

        $slug = app(SlugService::class)->create($request->input('name'), 0, Company::class);

        $request->merge(compact('slug'));

        event(new CreatedContentEvent(COMPANY_MODULE_SCREEN_NAME, $request, $company));

        $this->activityLogRepository->createOrUpdate([
            'action' => 'create_company',
            'reference_name' => $company->name,
            'reference_url' => route('public.account.companies.edit', $company->id),
        ]);

        EmailHandler::setModule(JOB_BOARD_MODULE_SCREEN_NAME)
            ->setVariableValues([
                'company_name' => $company->name,
                'company_url' => route('companies.edit', $company->id),
                'employer_name' => $account->name,
            ])
            ->sendUsingTemplate('new-company-profile-created');

        return $response
            ->setNextUrl(route('public.account.companies.edit', $company->id))
            ->setMessage(__('Create company profile successfully!'));
    }

    protected function handleUpload(Request $request)
    {
        if ($request->hasFile('logo_input')) {
            $result = RvMedia::handleUpload($request->file('logo_input'), 0, 'companies');
            if (! $result['error']) {
                $file = $result['data'];
                $request->merge(['logo' => $file->url]);
            }
        }

        if ($request->hasFile('cover_image_input')) {
            $result = RvMedia::handleUpload($request->file('cover_image_input'), 0, 'companies');
            if (! $result['error']) {
                $file = $result['data'];
                $request->merge(['cover_image' => $file->url]);
            }
        }

        return $request;
    }

    protected function getCompany(int $id, int $accountId)
    {
        return $this->companyRepository
            ->select(['jb_companies.*'])
            ->where('id', $id)
            ->whereHas('accounts', function ($query) use ($accountId) {
                $query->where('account_id', $accountId);
            })
            ->first();
    }

    public function edit(int|string $id, FormBuilder $formBuilder)
    {
        /**
         * @var Account $account
         */
        $account = auth('account')->user();

        $company = $this->getCompany($id, $account->getKey());
        if (! $company) {
            abort(404);
        }

        $title = __('Edit company ":name"', ['name' => $company->name]);
        SeoHelper::setTitle($title);
        PageTitle::setTitle($title);

        Theme::breadcrumb()
            ->add(__('Home'), route('public.index'))
            ->add(__('My Profile'), route('public.account.dashboard'))
            ->add(__('Companies'), route('public.account.companies.index'))
            ->add($title);

        Assets::addStyles(['datetimepicker'])
            ->addScripts([
                'moment',
                'datetimepicker',
                'jquery-ui',
                'input-mask',
                'blockui',
            ]);

        return $formBuilder
            ->create(CompanyForm::class, ['model' => $company])
            ->renderForm();
    }

    public function update(int|string $id, AccountCompanyRequest $request, BaseHttpResponse $response)
    {
        /**
         * @var Account $account
         */
        $account = auth('account')->user();

        $company = $this->getCompany($id, $account->getKey());
        if (! $company) {
            abort(404);
        }

        $request->except([
            'status',
            'logo',
            'cover_image',
            'is_featured',
        ]);

        $request = $this->handleUpload($request);

        $company->fill($request->input());
        $company->save();

        event(new UpdatedContentEvent(COMPANY_MODULE_SCREEN_NAME, $request, $company));

        $this->activityLogRepository->createOrUpdate([
            'action' => 'update_company',
            'reference_name' => $company->name,
            'reference_url' => route('public.account.companies.edit', $company->id),
        ]);

        return $response
            ->setNextUrl(route('public.account.companies.edit', $company->id))
            ->setMessage(__('Update company profile successfully!'));
    }

    public function destroy(int|string $id, Request $request, BaseHttpResponse $response)
    {
        try {
            /**
             * @var Account $account
             */
            $account = auth('account')->user();

            $company = $this->getCompany($id, $account->getKey());
            if (! $company) {
                abort(404);
            }
            $this->companyRepository->delete($company);

            event(new DeletedContentEvent(COMPANY_MODULE_SCREEN_NAME, $request, $company));

            $this->activityLogRepository->createOrUpdate([
                'action' => 'delete_company',
                'reference_name' => $company->name,
            ]);

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}

<?php

namespace Botble\JobBoard\Http\Controllers\Fronts;

use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\EmailHandler;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\JobBoard\Enums\JobStatusEnum;
use Botble\JobBoard\Enums\ModerationStatusEnum;
use Botble\JobBoard\Events\JobPublishedEvent;
use Botble\JobBoard\Facades\JobBoardHelper;
use Botble\JobBoard\Forms\Fronts\JobForm;
use Botble\JobBoard\Http\Requests\AccountJobRequest;
use Botble\JobBoard\Models\Account;
use Botble\JobBoard\Models\CustomFieldValue;
use Botble\JobBoard\Models\Job;
use Botble\JobBoard\Repositories\Interfaces\AccountActivityLogInterface;
use Botble\JobBoard\Repositories\Interfaces\AccountInterface;
use Botble\JobBoard\Repositories\Interfaces\AnalyticsInterface;
use Botble\JobBoard\Repositories\Interfaces\JobApplicationInterface;
use Botble\JobBoard\Repositories\Interfaces\JobInterface;
use Botble\JobBoard\Repositories\Interfaces\TagInterface;
use Botble\JobBoard\Services\StoreTagService;
use Botble\JobBoard\Tables\Fronts\JobTable;
use Botble\Media\Facades\RvMedia;
use Botble\Optimize\Facades\OptimizerHelper;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Carbon\Carbon;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AccountJobController extends Controller
{
    public function __construct(
        Repository $config,
        protected AccountInterface $accountRepository,
        protected JobInterface $jobRepository,
        protected AccountActivityLogInterface $activityLogRepository,
        protected JobApplicationInterface $applicationRepository,
        protected AnalyticsInterface $analyticsRepository
    ) {
        Assets::setConfig($config->get('plugins.job-board.assets'));

        OptimizerHelper::disable();
    }

    public function index(JobTable $table)
    {
        page_title()->setTitle(__('Manage Jobs'));

        Theme::breadcrumb()
            ->add(__('Home'), route('public.index'))
            ->add(__('My Profile'), route('public.account.dashboard'))
            ->add(__('Manage Jobs'));

        SeoHelper::setTitle(__('Manage Jobs'));

        return $table->render(JobBoardHelper::viewPath('dashboard.table.base'));
    }

    public function create(FormBuilder $formBuilder, BaseHttpResponse $response)
    {
        /**
         * @var Account $account
         */
        $account = auth('account')->user();

        if (! $account->canPost()) {
            return $response
                ->setError()
                ->setNextUrl(route('public.account.packages'))
                ->setMessage(__('Please purchase a package to post a job.'));
        }

        if (! $account->companies()->exists()) {
            return $response
                ->setError()
                ->setNextUrl(route('public.account.companies.create'))
                ->setMessage(__('Please update your company information first.'));
        }

        page_title()->setTitle(__('Post a job'));

        SeoHelper::setTitle(__('Post a job'));

        return $formBuilder->create(JobForm::class)->renderForm();
    }

    public function store(AccountJobRequest $request, BaseHttpResponse $response, StoreTagService $storeTagService)
    {
        /**
         * @var Account $account
         */
        $account = auth('account')->user();

        if (! $account->canPost()) {
            return $response->setNextUrl(route('public.account.jobs.index'));
        }

        $this->processRequestData($request);

        $request->except([
            'is_featured',
            'moderation_status',
            'never_expired',
        ]);

        $request->merge([
            'expire_date' => Carbon::now()->addDays(JobBoardHelper::jobExpiredDays()),
            'author_id' => $account->getAuthIdentifier(),
            'author_type' => Account::class,
        ]);

        if (! $request->has('employer_colleagues')) {
            $request->merge(['employer_colleagues' => []]);
        }

        $job = $this->jobRepository->getModel();
        $job->fill($request->input());

        if (JobBoardHelper::isEnabledJobApproval()) {
            $job->moderation_status = ModerationStatusEnum::PENDING;
        } else {
            $job->moderation_status = ModerationStatusEnum::APPROVED;

            event(new JobPublishedEvent($job));
        }

        $job = $this->jobRepository->createOrUpdate($job);

        $customFields = CustomFieldValue::formatCustomFields($request->input('custom_fields') ?? []);

        $job->customFields()
            ->whereNotIn('id', collect($customFields)->pluck('id')->all())
            ->delete();

        $job->customFields()->saveMany($customFields);

        $job->skills()->sync($request->input('skills', []));
        $job->jobTypes()->sync($request->input('jobTypes', []));
        $job->categories()->sync($request->input('categories', []));

        $storeTagService->execute($request, $job);

        event(new CreatedContentEvent(JOB_MODULE_SCREEN_NAME, $request, $job));

        $this->activityLogRepository->createOrUpdate([
            'action' => 'create_job',
            'reference_name' => $job->name,
            'reference_url' => route('public.account.jobs.edit', $job->id),
        ]);

        if (JobBoardHelper::isEnabledCreditsSystem() && $account->credits > 0) {
            $account->credits--;
            $account->save();
        }

        if ($job->status == JobStatusEnum::PUBLISHED) {
            $this->sendEmailAboutNewJobToAdmin($job);
        }

        return $response
            ->setPreviousUrl(route('public.account.jobs.index'))
            ->setNextUrl(route('public.account.jobs.edit', $job->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    protected function sendEmailAboutNewJobToAdmin(Job $job)
    {
        EmailHandler::setModule(JOB_BOARD_MODULE_SCREEN_NAME)
            ->setVariableValues([
                'job_name' => $job->name,
                'job_url' => route('jobs.edit', $job->id),
                'job_author' => $job->author->name,
            ])
            ->sendUsingTemplate('new-job-posted');
    }

    public function edit(int|string $id, FormBuilder $formBuilder, Request $request)
    {
        $job = $this->jobRepository->findOrFail($id);

        if (! $this->canManageJob($job)) {
            abort(404);
        }

        event(new BeforeEditContentEvent($request, $job));

        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $job->name]));

        return $formBuilder
            ->create(JobForm::class, ['model' => $job])
            ->renderForm();
    }

    protected function canManageJob(Job $job): bool
    {
        /**
         * @var Account $account
         */
        $account = auth('account')->user();
        if (! $account->isEmployer()) {
            return false;
        }

        if ($job->company_id && in_array($job->company_id, $account->companies()->pluck('id')->all())) {
            return true;
        }

        return $account->id == $job->author_id && $job->author_type == Account::class;
    }

    public function update(int|string $id, AccountJobRequest $request, BaseHttpResponse $response, StoreTagService $storeTagService)
    {
        $job = $this->jobRepository->findOrFail($id);

        if (! $this->canManageJob($job)) {
            abort(404);
        }

        $this->processRequestData($request);

        $request->except([
            'is_featured',
            'moderation_status',
            'never_expired',
            'expire_date',
        ]);

        if (! $request->has('employer_colleagues')) {
            $request->merge(['employer_colleagues' => []]);
        }

        if ($job->status != JobStatusEnum::PUBLISHED && $request->input('status') == JobStatusEnum::PUBLISHED) {
            $this->sendEmailAboutNewJobToAdmin($job);
        }

        $job->fill($request->input());

        $this->jobRepository->createOrUpdate($job);

        $customFields = CustomFieldValue::formatCustomFields($request->input('custom_fields') ?? []);

        $job->customFields()
            ->whereNotIn('id', collect($customFields)->pluck('id')->all())
            ->delete();

        $job->customFields()->saveMany($customFields);

        $job->skills()->sync($request->input('skills', []));
        $job->jobTypes()->sync($request->input('jobTypes', []));
        $job->categories()->sync($request->input('categories', []));

        $storeTagService->execute($request, $job);

        event(new UpdatedContentEvent(JOB_MODULE_SCREEN_NAME, $request, $job));

        $this->activityLogRepository->createOrUpdate([
            'action' => 'update_job',
            'reference_name' => $job->name,
            'reference_url' => route('public.account.jobs.edit', $job->id),
        ]);

        return $response
            ->setPreviousUrl(route('public.account.jobs.index'))
            ->setNextUrl(route('public.account.jobs.edit', $job->id))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    protected function processRequestData(Request $request): Request
    {
        if ($request->hasFile('featured_image_input')) {
            $account = auth('account')->user();
            $result = RvMedia::handleUpload($request->file('featured_image_input'), 0, $account);
            if (! $result['error']) {
                $file = $result['data'];
                $request->merge(['featured_image' => $file->url]);
            }
        }

        $shortcodeCompiler = shortcode()->getCompiler();

        $request->merge([
            'content' => $shortcodeCompiler->strip(
                $request->input('content'),
                $shortcodeCompiler->whitelistShortcodes()
            ),
        ]);

        $except = [
            'status',
            'is_featured',
        ];

        foreach ($except as $item) {
            $request->request->remove($item);
        }

        return $request;
    }

    public function destroy(int $id, BaseHttpResponse $response)
    {
        $job = $this->jobRepository->findOrFail($id);

        if (! $this->canManageJob($job)) {
            abort(404);
        }

        $this->jobRepository->delete($job);

        $this->activityLogRepository->createOrUpdate([
            'action' => 'delete_job',
            'reference_name' => $job->name,
        ]);

        return $response->setMessage(__('Delete job successfully!'));
    }

    public function renew(int $id, BaseHttpResponse $response)
    {
        $job = $this->jobRepository->findOrFail($id);

        if (! $this->canManageJob($job)) {
            abort(404);
        }
        /**
         * @var Account $account
         */
        $account = auth('account')->user();
        if ($account->credits < 1) {
            return $response->setError()->setMessage(__('You don\'t have enough credit to renew this job!'));
        }

        $job->expire_date = $job->expire_date->addDays(JobBoardHelper::jobExpiredDays());
        $job->save();

        if (JobBoardHelper::isEnabledCreditsSystem() && $account->credits > 0) {
            $account->credits--;
            $account->save();
        }

        $this->activityLogRepository->createOrUpdate([
            'action' => 'renew_job',
            'reference_name' => $job->name,
        ]);

        return $response->setMessage(__('Renew job successfully'));
    }

    public function analytics(int $id)
    {
        $job = $this->jobRepository->findOrFail($id);

        if (! $this->canManageJob($job)) {
            abort(404);
        }

        $job->loadCount([
            'savedJobs',
            'applicants',
        ]);

        $numberSaved = $job->saved_jobs_count;
        $applicants = $job->applicants_count;
        $viewsToday = $this->analyticsRepository->getTodayViews($job->id);
        $referrers = $this->analyticsRepository->getReferrers($job->id);
        $countries = $this->analyticsRepository->getCountriesViews($job->id);

        $title = __('Analytics for job: :name', ['name' => $job->name]);

        SeoHelper::setTitle($title);
        page_title()->setTitle($title);

        $data = compact('job', 'viewsToday', 'numberSaved', 'applicants', 'referrers', 'countries', 'title');

        return JobBoardHelper::view('dashboard.jobs.analytics', $data);
    }

    public function appliedJobs(Request $request)
    {
        /**
         * @var Account $account
         */
        $account = auth('account')->user();

        $with = [
            'job',
            'job.slugable',
            'job.jobTypes',
            'job.jobExperience',
            'job.company',
            'job.company.slugable',
        ];

        if (is_plugin_active('location')) {
            $with = array_merge($with, ['job.state', 'job.city']);
        }

        $applications = $this->applicationRepository->getModel()
            ->whereHas('job')
            ->where('account_id', $account->getKey())
            ->with($with);

        switch ($request->input('order_by')) {
            case 'newest':
                $applications = $applications->orderBy('created_at', 'DESC');

                break;
            case 'oldest':
                $applications = $applications->orderBy('created_at', 'ASC');

                break;
            case 'random':
                $applications = $applications->inRandomOrder();

                break;
        }

        $applications = $applications->paginate(10);

        SeoHelper::setTitle(__('Applied Jobs'));
        Theme::breadcrumb()
            ->add(__('Home'), route('public.index'))
            ->add(__('My Profile'), route('public.account.overview'))
            ->add(__('Applied Jobs'));

        $data = compact('account', 'applications');

        return JobBoardHelper::scope('account.jobs.applied', $data);
    }

    public function savedJobs(Request $request)
    {
        /**
         * @var Account $account
         */
        $account = auth('account')->user();

        $condition = JobBoardHelper::getJobDisplayQueryConditions();
        $with = [
            'slugable',
            'company',
        ];

        if (is_plugin_active('location')) {
            $with = array_merge($with, ['city', 'state']);
        }

        $jobs = $this->jobRepository
            ->select(['jb_jobs.*'])
            ->notExpired()
            ->where($condition)
            ->whereHas('savedJobs', function ($query) use ($account) {
                $query->where('jb_saved_jobs.account_id', $account->getKey());
            })
            ->addApplied()
            ->with($with);

        if ($category = $request->integer('category')) {
            $jobs->whereHas('categories', function ($query) use ($category) {
                $query->where('jb_categories.id', $category);
            });
        }

        switch ($request->input('order_by')) {
            case 'newest':
                $jobs = $jobs->orderBy('created_at', 'DESC');

                break;
            case 'oldest':
                $jobs = $jobs->orderBy('created_at', 'ASC');

                break;
            case 'random':
                $jobs = $jobs->inRandomOrder();

                break;
        }

        $jobs = $jobs->paginate();

        SeoHelper::setTitle(__('Saved Jobs'));
        Theme::breadcrumb()
            ->add(__('Home'), route('public.index'))
            ->add(__('My Profile'), route('public.account.overview'))
            ->add(__('Saved Jobs'));

        $data = compact('account', 'jobs');

        return JobBoardHelper::scope('account.jobs.saved', $data);
    }

    public function savedJob(Request $request, BaseHttpResponse $response, ?int $id = null)
    {
        if (! $id) {
            $id = $request->input('job_id');
        }

        if (! $id) {
            abort(404);
        }

        $condition = ['jb_jobs.id' => $id] + JobBoardHelper::getJobDisplayQueryConditions();

        /**
         * @var Account $account
         */
        $account = auth('account')->user();

        $job = $this->jobRepository
            ->select(['jb_jobs.id', 'jb_jobs.name'])
            ->notExpired()
            ->where($condition)
            ->addSaved()
            ->firstOrFail();

        if (! $job->is_saved) {
            $account->savedJobs()->attach($job->id);
            $message = __('Job :job added from saved jobs!', ['job' => $job->name]);
        } else {
            $account->savedJobs()->detach($job->id);
            $message = __('Job :job removed from saved jobs!', ['job' => $job->name]);
        }

        return $response
            ->setData([
                'is_saved' => ! $job->is_saved,
                'count' => $account->savedJobs()->count(),
            ])
            ->setMessage($message);
    }

    public function getAllTags(TagInterface $tagRepository): array
    {
        return $tagRepository->pluck('name');
    }
}

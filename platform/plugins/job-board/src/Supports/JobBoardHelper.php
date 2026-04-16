<?php

namespace Botble\JobBoard\Supports;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Models\BaseQueryBuilder;
use Botble\JobBoard\Enums\AccountTypeEnum;
use Botble\JobBoard\Enums\JobStatusEnum;
use Botble\JobBoard\Enums\ModerationStatusEnum;
use Botble\JobBoard\Models\Account;
use Botble\Page\Models\Page;
use Botble\Page\Repositories\Interfaces\PageInterface;
use Botble\Theme\Facades\Theme;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class JobBoardHelper
{
    protected string|null $jobsPageURL = null;

    protected string|null $jobCategoriesPageURL = null;

    protected string|null $jobCandidatesPageURL = null;

    protected string|null $jobCompaniesPageURL = null;

    public function isGuestApplyEnabled(): bool
    {
        return setting('job_board_enable_guest_apply', 1) == 1;
    }

    public function isRegisterEnabled(): bool
    {
        return setting('job_board_enabled_register', 1) == 1;
    }

    public function jobExpiredDays(): int
    {
        $days = (int)setting('job_expired_after_days');

        if ($days > 0) {
            return $days;
        }

        return 45;
    }

    public function isEnabledCreditsSystem(): bool
    {
        return setting('job_board_enable_credits_system', 1) == 1;
    }

    public function isEnabledJobApproval(): bool
    {
        return setting('job_board_enable_post_approval', 1) == 1;
    }

    public function getThousandSeparatorForInputMask(): string
    {
        return ',';
    }

    public function getDecimalSeparatorForInputMask(): string
    {
        return '.';
    }

    public function getJobDisplayQueryConditions(): array
    {
        return [
            'jb_jobs.moderation_status' => ModerationStatusEnum::APPROVED,
            'jb_jobs.status' => JobStatusEnum::PUBLISHED,
        ];
    }

    public function postedDateRanges(): array
    {
        return [
            'last_hour' => [
                'name' => __('Last hour'),
                'end' => Carbon::now()->subHour(),
            ],
            'last_24_hours' => [
                'name' => __('Last 24 hours'),
                'end' => Carbon::now()->subDay(),
            ],
            'last_7_days' => [
                'name' => __('Last 7 days'),
                'end' => Carbon::now()->subWeek(),
            ],
            'last_14_days' => [
                'name' => __('Last 14 days'),
                'end' => Carbon::now()->subWeeks(2),
            ],
            'last_1_month' => [
                'name' => __('Last 1 month'),
                'end' => Carbon::now()->subMonth(),
            ],
        ];
    }

    public function getAssetVersion(): string
    {
        return '1.2.0';
    }

    public function viewPath(string $view): string
    {
        $themeView = Theme::getThemeNamespace(Theme::getConfig('containerDir.view') . '.job-board.' . $view);

        if (view()->exists($themeView)) {
            return $themeView;
        }

        return 'plugins/job-board::themes.' . $view;
    }

    public function view(string $view, array $data = []): Factory|View|Application
    {
        return view($this->viewPath($view), $data);
    }

    public function scope(string $view, array $data = []): Response|string
    {
        $path = Theme::getThemeNamespace(Theme::getConfig('containerDir.view') . '.job-board.' . $view);

        if (view()->exists($path)) {
            return Theme::scope('job-board.' . $view, $data)->render();
        }

        return view('plugins/job-board::themes.' . $view, $data)->render();
    }

    protected function getPage($pageId): ?Page
    {
        if (! $pageId) {
            return null;
        }

        return app(PageInterface::class)->getFirstBy([
            'id' => $pageId,
            'status' => BaseStatusEnum::PUBLISHED,
        ], ['id', 'name'], ['slugable']);
    }

    public function getJobsPageURL(): string|null
    {
        if ($this->jobsPageURL) {
            return $this->jobsPageURL;
        }

        $page = $this->getPage(theme_option('job_list_page_id'));

        $this->jobsPageURL = $page?->url;

        return $this->jobsPageURL;
    }

    public function getJobCategoriesPageURL(): string|null
    {
        if ($this->jobCategoriesPageURL) {
            return $this->jobCategoriesPageURL;
        }

        $page = $this->getPage(theme_option('job_categories_page_id'));

        $this->jobCategoriesPageURL = $page?->url;

        return $this->jobCategoriesPageURL;
    }

    public function getJobCompaniesPageURL(): string|null
    {
        if ($this->jobCompaniesPageURL) {
            return $this->jobCompaniesPageURL;
        }

        $page = $this->getPage(theme_option('job_companies_page_id'));

        $this->jobCompaniesPageURL = $page?->url;

        return $this->jobCompaniesPageURL;
    }

    public function getJobCandidatesPageURL(): string|null
    {
        if ($this->isDisabledPublicProfile()) {
            return route('public.index');
        }

        if ($this->jobCandidatesPageURL) {
            return $this->jobCandidatesPageURL;
        }

        $page = $this->getPage(theme_option('job_candidates_page_id'));

        $this->jobCandidatesPageURL = $page?->url;

        return $this->jobCandidatesPageURL;
    }

    public function getJobFilters(array|Request $inputs): array
    {
        if ($inputs instanceof Request) {
            $inputs = $inputs->input();
        }

        $params = [
            'keyword' => BaseHelper::stringify(Arr::get($inputs, 'keyword')),
            'city_id' => (int)Arr::get($inputs, 'city_id'),
            'location' => BaseHelper::stringify(Arr::get($inputs, 'location')),
            'job_categories' => (array)Arr::get($inputs, 'job_categories', []),
            'job_tags' => (array)Arr::get($inputs, 'job_tags', []),
            'job_types' => (array)Arr::get($inputs, 'job_types', []),
            'job_experiences' => (array)Arr::get($inputs, 'job_experiences', []),
            'job_skills' => (array)Arr::get($inputs, 'job_skills', []),
            'offered_salary_from' => BaseHelper::stringify(Arr::get($inputs, 'offered_salary_from')),
            'offered_salary_to' => BaseHelper::stringify(Arr::get($inputs, 'offered_salary_to')),
            'date_posted' => BaseHelper::stringify(Arr::get($inputs, 'date_posted')),
            'page' => (int) Arr::get($inputs, 'page', 1),
            'per_page' => (int) Arr::get($inputs, 'per_page', 12),
        ];

        $validator = Validator::make($params, [
            'keyword' => 'nullable|string|max:255',
            'location' => 'nullable|string',
            'city_id' => 'nullable|numeric',
            'job_categories' => 'nullable|array',
            'job_tags' => 'nullable|array',
            'job_types' => 'nullable|array',
            'job_experiences' => 'nullable|array',
            'job_skills' => 'nullable|array',
            'offered_salary_from' => 'nullable|numeric',
            'offered_salary_to' => 'nullable|numeric',
            'date_posted' => 'nullable|string',
            'page' => 'nullable|integer|gt:0',
            'per_page' => 'nullable|integer|gt:0',
        ]);

        return $validator->validated();
    }

    /**
     * @deprecated
     */
    public function jobFilterParamsValidated(array $params): bool
    {
        return true;
    }

    /**
     * @deprecated
     */
    public function companyFilterParamsValidated(array $params): bool
    {
        return true;
    }

    public function getCompanyFilterParams(array|Request $inputs): array
    {
        if ($inputs instanceof Request) {
            $inputs = $inputs->input();
        }

        $params = [
            'keyword' => BaseHelper::stringify(Arr::get($inputs, 'keyword')),
            'sort_by' => (int)Arr::get($inputs, 'sort_by', 'newest') ?: 'newest',
            'page' => (int) Arr::get($inputs, 'page', 1) ?: 1,
            'per_page' => (int) Arr::get($inputs, 'per_page', 12) ?: 12,
        ];

        $validator = Validator::make($params, [
            'per_page' => 'nullable|numeric',
            'keyword' => 'nullable|string',
            'sort_by' => 'nullable|string|in:newest,oldest',
            'page' => 'nullable|integer|gt:0',
            'page_page' => 'nullable|integer|gt:0',
        ]);

        return $validator->validated();
    }

    public function filterCandidates(array $params): LengthAwarePaginator
    {
        $data = Validator::validate($params, [
            'keyword' => ['nullable', 'string', 'max:200'],
            'sort_by' => ['nullable', Rule::in(array_keys($this->getSortByParams()))],
            'page' => ['nullable', 'numeric', 'min:1'],
            'per_page' => ['nullable', 'numeric', 'min:1'],
        ]);

        $with = [
            'avatar',
            'slugable',
        ];

        if (is_plugin_active('location')) {
            $with = array_merge($with, [
                'country',
                'state',
            ]);
        }

        $candidates = Account::query()
            ->where([
                'is_public_profile' => 1,
                'type' => AccountTypeEnum::JOB_SEEKER,
            ])
            ->with($with);

        $sortBy = match ($data['sort_by'] ?? 'newest') {
            'oldest' => [
                'is_featured' => 'DESC',
                'created_at' => 'ASC',
            ],
            default => [
                'is_featured' => 'DESC',
                'created_at' => 'DESC',
            ],
        };

        foreach ($sortBy as $column => $direction) {
            $candidates = $candidates->orderBy($column, $direction);
        }

        if (setting('verify_account_email', 0)) {
            $candidates = $candidates->whereNotNull('confirmed_at');
        }

        if (isset($data['keyword']) && $keyword = $data['keyword']) {
            if (strlen($keyword) === 1) {
                $candidates = $candidates->where('first_name', 'LIKE', $keyword . '%');
            } else {
                $candidates = $candidates->where(function (BaseQueryBuilder $query) use ($keyword) {
                    $query
                        ->addSearch('first_name', $keyword, false, false)
                        ->addSearch('last_name', $keyword, false);
                });
            }
        }

        if (self::isEnabledReview()) {
            $candidates = $candidates
                ->withAvg('reviews', 'star')
                ->withCount('reviews');
        }

        return $candidates->paginate($data['per_page'] ?? 12);
    }

    public function getSortByParams(): array
    {
        return apply_filters('job_board_sort_by_params', [
            'newest' => __('Newest'),
            'oldest' => __('Oldest'),
        ]);
    }

    public function getPerPageParams(): array
    {
        return [12, 24, 36];
    }

    public function isEnabledReview(): bool
    {
        return (bool) setting('job_board_is_enabled_review_feature', true);
    }

    public function isDisabledPublicProfile(): bool
    {
        return (bool) setting('job_board_disabled_public_profile', false);
    }

    public function getMapCenterLatLng(): array
    {
        $center = theme_option('latitude_longitude_center_on_jobs_page');
        $latLng = [];
        if ($center) {
            $center = explode(',', $center);
            if (count($center) == 2) {
                $latLng = [trim($center[0]), trim($center[1])];
            }
        }

        if (! $latLng) {
            $latLng = [43.615134, -76.393186];
        }

        return $latLng;
    }

    public function isZipCodeEnabled(): bool
    {
        return (bool) setting('job_board_zip_code_enabled', false);
    }

    public function hideCompanyEmailEnabled(): bool
    {
        return (bool) setting('job_board_hide_company_email_enabled', false);
    }
}

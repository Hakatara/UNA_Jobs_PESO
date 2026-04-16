<?php

namespace Botble\JobBoard\Repositories\Eloquent;

use Botble\Base\Models\BaseQueryBuilder;
use Botble\JobBoard\Facades\JobBoardHelper;
use Botble\JobBoard\Models\Job;
use Botble\JobBoard\Repositories\Interfaces\JobInterface;
use Botble\Language\Facades\Language;
use Botble\Location\Facades\Location;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class JobRepository extends RepositoriesAbstract implements JobInterface
{
    public function getJobs(array $filters = [], array $params = [])
    {
        $filters = JobBoardHelper::getJobFilters($filters);

        $filters = array_merge([
            'keyword' => null,
            'city_id' => null,
            'city' => null,
            'location' => null,
            'job_categories' => [],
            'job_tags' => [],
            'job_types' => [],
            'job_experiences' => [],
            'job_skills' => [],
            'offered_salary_from' => null,
            'offered_salary_to' => null,
            'date_posted' => null,
        ], $filters);

        $with = [
            'slugable',
            'tags',
            'jobTypes',
            'jobExperience',
            'company',
            'company.slugable',
        ];

        if (is_plugin_active('location')) {
            $with = array_merge($with, array_keys(Location::getSupported(Job::class)));
        }

        $params = array_merge([
            'condition' => JobBoardHelper::getJobDisplayQueryConditions(),
            'order_by' => [
                'created_at' => 'DESC',
                'is_featured' => 'DESC',
            ],
            'take' => null,
            'paginate' => [
                'per_page' => 20,
                'current_paged' => 1,
            ],
            'select' => ['jb_jobs.*'],
            'with' => $with,
            'withCount' => ['applicants'],
        ], $params);

        $this->model = $this->originalModel;

        // @phpstan-ignore-next-line
        $this->model = $this->model->select($params['select'])->notExpired();

        if ($keyword = $filters['keyword']) {
            if (
                is_plugin_active('language') &&
                is_plugin_active('language-advanced') &&
                Language::getCurrentLocale() != Language::getDefaultLocale()
            ) {
                $this->model = $this->model
                    ->where(function (BaseQueryBuilder $query) use ($keyword) {
                        $query
                            ->whereHas('translations', function (BaseQueryBuilder $query) use ($keyword) {
                                $query->addSearch('name', $keyword, false, false);
                            })
                            ->where(function (BaseQueryBuilder $query) use ($keyword) {
                                $query
                                    ->where('hide_company', false)
                                    ->orWhereHas('company', function (BaseQueryBuilder $query) use ($keyword) {
                                        $query->addSearch('name', false, false);
                                    });
                            })
                            ->orWhereHas('tags.translations', function (BaseQueryBuilder $query) use ($keyword) {
                                $query->addSearch('name', $keyword, false, false);
                            })
                            ->orWhereHas('skills.translations', function (BaseQueryBuilder $query) use ($keyword) {
                                $query->addSearch('name', $keyword, false, false);
                            });
                    });
            } else {
                $this->model = $this->model
                    ->where(function (BaseQueryBuilder $query) use ($keyword) {
                        $query
                            ->addSearch('name', $keyword, false, false)
                            ->where(function (BaseQueryBuilder $query) use ($keyword) {
                                $query
                                    ->where('hide_company', false)
                                    ->orWhereHas('company', function (BaseQueryBuilder $query) use ($keyword) {
                                        $query->addSearch('name', false, false);
                                    });
                            })
                            ->orWhereHas('tags', function (BaseQueryBuilder $query) use ($keyword) {
                                $query->addSearch('name', $keyword, false, false);
                            })
                            ->orWhereHas('skills', function (BaseQueryBuilder $query) use ($keyword) {
                                $query->addSearch('name', $keyword, false, false);
                            });
                    });
            }
        }

        if (is_plugin_active('location') && ((int)$filters['city_id'] || $filters['location'])) {
            $this->model = Location::filter($this->model, (int)$filters['city_id'], $filters['location']);
        }

        // Filter jobs by categories
        $filters['job_categories'] = array_map('intval', array_filter($filters['job_categories']));

        if ($filters['job_categories']) {
            $this->model = $this->model->whereHas('categories', function (Builder $query) use ($filters) {
                $query->whereIn('jb_categories.id', $filters['job_categories']);
            });
        }

        // Filter jobs by tag
        $filters['job_tags'] = array_map('intval', array_filter($filters['job_tags']));
        if ($filters['job_tags']) {
            $this->model = $this->model->whereHas('tags', function (Builder $query) use ($filters) {
                $query->whereIn('jb_tags.id', $filters['job_tags']);
            });
        }
        // Filter job by types
        $filters['job_types'] = array_map('intval', array_filter($filters['job_types']));
        if ($filters['job_types']) {
            $this->model = $this->model->whereHas('jobTypes', function ($query) use ($filters) {
                return $query->whereIn('jb_job_types.id', $filters['job_types']);
            });
        }

        // Filter job by experiences
        $filters['job_experiences'] = array_map('intval', array_filter($filters['job_experiences']));
        if ($filters['job_experiences']) {
            $this->model = $this->model->whereIn('job_experience_id', $filters['job_experiences']);
        }

        // Filter job by offered salary
        if ($filters['offered_salary_from'] && $filters['offered_salary_from'] > 0) {
            $this->model = $this->model
                ->where(function ($query) use ($filters) {
                    $query->whereNull('salary_from')
                        ->orWhere('salary_from', '>=', $filters['offered_salary_from']);
                });
        }

        if ($filters['offered_salary_to'] && $filters['offered_salary_to'] > 0) {
            $this->model = $this->model->where('salary_to', '<=', $filters['offered_salary_to']);

            $this->model = $this->model
                ->where(function ($query) use ($filters) {
                    $query->whereNull('salary_to')
                        ->orWhere('salary_to', '<=', $filters['offered_salary_to']);
                });
        }

        // Filter job by skills
        $filters['job_skills'] = array_map('intval', array_filter($filters['job_skills']));
        if ($filters['job_skills']) {
            $this->model = $this->model->whereHas('skills', function ($query) use ($filters) {
                return $query->whereIn('jb_job_skills.id', $filters['job_skills']);
            });
        }

        if ($filters['date_posted'] && $date = Arr::get(JobBoardHelper::postedDateRanges(), $filters['date_posted'])) {
            if ($start = Arr::get($date, 'start')) {
                $this->model = $this->model->whereDate('created_at', '<=', $start);
            }
            if ($end = Arr::get($date, 'end')) {
                $this->model = $this->model->whereDate('created_at', '>=', $end);
            }
        }

        $this->model = $this->model->addSavedApplied();
        $params['select'] = []; // Reset select to avoid duplicate columns

        return $this->advancedGet($params);
    }

    public function getFeaturedJobs(int $limit = 10, array $with = [])
    {
        $params = [
            'condition' => [
                    'is_featured' => true,
                ] + JobBoardHelper::getJobDisplayQueryConditions(),
            'order_by' => [
                'created_at' => 'DESC',
            ],
            'take' => $limit,
        ];

        if ($with) {
            $params['with'] = $with;
        }

        return $this->getJobs([], $params);
    }

    public function getRecentJobs(int $limit = 10, array $with = [])
    {
        $params = [
            'order_by' => [
                'created_at' => 'DESC',
            ],
            'take' => $limit,
        ];
        if ($with) {
            $params['with'] = $with;
        }

        return $this->getJobs([], $params);
    }

    public function getPopularJobs(int $limit = 10, array $with = [])
    {
        $params = [
            'order_by' => [
                'views' => 'DESC',
                'created_at' => 'DESC',
            ],
            'take' => $limit,
        ];
        if ($with) {
            $params['with'] = $with;
        }

        return $this->getJobs([], $params);
    }

    public function countActiveJobs()
    {
        // @phpstan-ignore-next-line
        $data = $this->model
            ->notExpired()
            ->where(JobBoardHelper::getJobDisplayQueryConditions());

        return $this->applyBeforeExecuteQuery($data)->count('id');
    }
}

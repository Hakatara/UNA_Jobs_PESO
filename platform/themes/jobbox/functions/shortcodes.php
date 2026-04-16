<?php

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\Html;
use Botble\Blog\Repositories\Interfaces\PostInterface;
use Botble\Faq\Repositories\Interfaces\FaqCategoryInterface;
use Botble\Faq\Repositories\Interfaces\FaqInterface;
use Botble\JobBoard\Enums\AccountTypeEnum;
use Botble\JobBoard\Facades\JobBoardHelper;
use Botble\JobBoard\Models\Company;
use Botble\JobBoard\Models\Job;
use Botble\JobBoard\Repositories\Interfaces\AccountInterface;
use Botble\JobBoard\Repositories\Interfaces\CategoryInterface;
use Botble\JobBoard\Repositories\Interfaces\CompanyInterface;
use Botble\JobBoard\Repositories\Interfaces\JobExperienceInterface;
use Botble\JobBoard\Repositories\Interfaces\JobInterface;
use Botble\JobBoard\Repositories\Interfaces\JobSkillInterface;
use Botble\JobBoard\Repositories\Interfaces\JobTypeInterface;
use Botble\JobBoard\Repositories\Interfaces\PackageInterface;
use Botble\Location\Facades\Location;
use Botble\Location\Models\City;
use Botble\Location\Models\State;
use Botble\Shortcode\Compilers\Shortcode;
use Botble\Team\Repositories\Interfaces\TeamInterface;
use Botble\Testimonial\Models\Testimonial;
use Botble\Theme\Facades\Theme;
use Botble\Theme\Supports\ThemeSupport;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

app()->booted(function () {
    ThemeSupport::registerGoogleMapsShortcode();
    ThemeSupport::registerYoutubeShortcode();

    if (is_plugin_active('job-board')) {
        add_shortcode('search-box', __('Search box'), __('The big search box'), function (Shortcode $shortcode) {
            if ($shortcode->style === 'style-2') {
                $with = [
                    'slugable',
                    'metadata',
                ];

                $featureCompanies = app(CompanyInterface::class)
                    ->advancedGet([
                        'with' => $with,
                        'take' => (int)$shortcode->limit_company ?: Arr::first(JobBoardHelper::getPerPageParams()),
                        'order_by' => ['created_at' => 'DESC'],
                        'condition' => [
                            'status' => BaseStatusEnum::PUBLISHED,
                            'is_featured' => 1,
                        ],
                    ]);

                return Theme::partial('shortcodes.search-box', compact('shortcode', 'featureCompanies'));
            }

            if ($shortcode->style === 'style-3') {
                $categories = app(CategoryInterface::class)
                    ->advancedGet([
                        'withCount' => [
                            'jobs' => function (Builder $query) {
                                $query
                                    ->where(JobBoardHelper::getJobDisplayQueryConditions())
                                    ->notExpired();
                            },
                        ],
                        'condition' => [
                          'status' => BaseStatusEnum::PUBLISHED,
                        ],
                        'with' => [
                            'slugable',
                            'metadata',
                        ],
                    ]);

                return Theme::partial('shortcodes.search-box', compact('shortcode', 'categories'));
            }

            return Theme::partial('shortcodes.search-box', compact('shortcode'));
        });

        shortcode()->setAdminConfig('search-box', function (array $attributes) {
            return Theme::partial('shortcodes.search-box-admin-config', compact('attributes'));
        });

        add_shortcode('featured-job-categories', __('Featured job categories'), __('Featured job categories'), function (Shortcode $shortcode) {
            $categories = app(CategoryInterface::class)
                ->getFeaturedCategories((int)$shortcode->limit_category ?: Arr::first(JobBoardHelper::getPerPageParams()), [
                    'jobs' => function (Builder $query) {
                        $query
                            ->where(JobBoardHelper::getJobDisplayQueryConditions())
                            ->notExpired()
                            ->addApplied()
                            ->orderBy('is_featured', 'DESC')
                            ->latest();
                    },
                    'metadata',
                ]);

            return Theme::partial('shortcodes.featured-job-categories', compact('shortcode', 'categories'));
        });

        shortcode()->setAdminConfig('featured-job-categories', function (array $attributes) {
            return Theme::partial('shortcodes.featured-job-categories-admin-config', compact('attributes'));
        });

        add_shortcode('job-categories', __('Job categories'), __('Job categories'), function (Shortcode $shortcode) {
            $categories = app(CategoryInterface::class)
                ->advancedGet(
                    [
                        'paginate' => [
                            'per_page' => (int)$shortcode->limit_category ?: 8,
                            'current_paged' => null,
                        ],
                        'condition' => [
                            'status' => BaseStatusEnum::PUBLISHED,
                        ],
                        'withCount' => [
                            'jobs' => function (Builder $query) {
                                $query
                                    ->where(JobBoardHelper::getJobDisplayQueryConditions())
                                    ->notExpired();
                            },
                        ],
                    ]
                );

            return Theme::partial('shortcodes.job-categories', compact('shortcode', 'categories'));
        });

        shortcode()->setAdminConfig('job-categories', function (array $attributes) {
            return Theme::partial('shortcodes.job-categories-admin-config', compact('attributes'));
        });

        add_shortcode('apply-banner', __('Apply banner'), __('Apply banner show form apply'), function (Shortcode $shortcode) {
            $subtitleText = '';

            if ($shortcode->highlight_sub_title_text) {
                $oldHighLightText = explode(',', $shortcode->highlight_sub_title_text);
                $newHighlightText = array_map(function ($value) {
                    return '<span class="color-brand-1">' . $value . '</span>';
                }, $oldHighLightText);

                $subtitleText = str_replace($oldHighLightText, $newHighlightText, $shortcode->subtitle);
            }

            return Theme::partial('shortcodes.apply-banner', compact('shortcode', 'subtitleText'));
        });

        shortcode()->setAdminConfig('apply-banner', function (array $attributes) {
            return Theme::partial('shortcodes.apply-banner-admin-config', compact('attributes'));
        });

        add_shortcode('job-tabs', __('Job tabs'), __('Job tabs'), function (Shortcode $shortcode) {
            $with = [
                'slugable',
                'jobTypes',
                'company',
                'company.slugable',
                'jobExperience',
            ];

            if (is_plugin_active('location')) {
                $with = array_merge($with, array_keys(Location::getSupported(Job::class)));
            }

            $featuredJobs = app(JobInterface::class)->getFeaturedJobs(10, $with);
            $recentJobs = app(JobInterface::class)->getRecentJobs(10, $with);
            $popularJobs = app(JobInterface::class)->getPopularJobs(10, $with);

            return Theme::partial('shortcodes.job-tabs', compact('shortcode', 'featuredJobs', 'recentJobs', 'popularJobs'));
        });

        shortcode()->setAdminConfig('job-tabs', function (array $attributes) {
            return Theme::partial('shortcodes.job-tabs-admin-config', compact('attributes'));
        });

        add_shortcode('job-of-the-day', __('Job of the day'), __('Job of the day'), function (Shortcode $shortcode) {
            $categoryIds = [];

            if ($shortcode->job_categories) {
                $categoryIds = explode(',', $shortcode->job_categories);
            }

            if (empty($categoryIds)) {
                return null;
            }

            $categories = app(CategoryInterface::class)
                ->advancedGet([
                    'with' => ['metadata'],
                    'condition' => [
                        'IN' => ['id', 'IN', $categoryIds],
                    ],
                ]);

            $with = [
                'slugable',
                'company',
                'company.slugable',
                'jobTypes',
                'tags',
                'tags.slugable',
                'skills',
            ];

            if ((is_plugin_active('location'))) {
                $with = array_merge($with, [
                    'country',
                    'state',
                    'city',
                ]);
            }

            $jobs = app(JobInterface::class)
                ->getJobs(
                    [
                        'job_categories' => $categories->pluck('id')->all(),
                    ],
                    [
                        'with' => $with,
                        'take' => 8,
                    ]
                );

            return Theme::partial('shortcodes.job-of-the-day', compact('shortcode', 'categories', 'jobs'));
        });

        Assets::addStylesDirectly('vendor/core/core/base/libraries/tagify/tagify.css');

        shortcode()->setAdminConfig('job-of-the-day', function (array $attributes) {
            $categories = app(CategoryInterface::class)->getCategories([]);

            return Html::script('vendor/core/core/base/libraries/tagify/tagify.js') .
                Html::script('vendor/core/core/base/js/tags.js') .
                Theme::partial('shortcodes.job-of-the-day-admin-config', compact('attributes', 'categories'));
        });

        add_shortcode('job-grid', __('Job grid banner'), __('Job grid banner'), function (Shortcode $shortcode) {
            return Theme::partial('shortcodes.job-grid', compact('shortcode'));
        });

        add_shortcode('company-information', __('Company Information'), __('Company Information'), function (Shortcode $shortcode) {
            return Theme::partial('shortcodes.company-information', compact('shortcode'));
        });

        shortcode()->setAdminConfig('company-information', function (array $attributes) {
            return Theme::partial('shortcodes.company-information-admin-config', compact('attributes'));
        });

        add_shortcode('company-about', __('Company About'), __('Company About'), function (Shortcode $shortcode) {
            return Theme::partial('shortcodes.company-about', compact('shortcode'));
        });

        shortcode()->setAdminConfig('company-about', function (array $attributes) {
            return Theme::partial('shortcodes.company-about-admin-config', compact('attributes'));
        });

        add_shortcode('job-grid', __('Job grid banner'), __('Job grid banner'), function (Shortcode $shortcode) {
            return Theme::partial('shortcodes.job-grid', compact('shortcode'));
        });

        shortcode()->setAdminConfig('job-grid', function (array $attributes) {
            return Theme::partial('shortcodes.job-grid-admin-config', compact('attributes'));
        });

        if (is_plugin_active('location')) {
            add_shortcode('job-by-location', __('Job by location'), __('Job by location'), function (Shortcode $shortcode) {
                $cityIds = array_filter(explode(',', $shortcode->city));
                $stateIds = array_filter(explode(',', $shortcode->state));

                if (empty($cityIds) && empty($stateIds)) {
                    return null;
                }

                $cities = collect([]);
                $states = collect([]);

                if (! empty($cityIds)) {
                    City::resolveRelationUsing('companies', function ($model) {
                        return $model->hasMany(Company::class, 'city_id');
                    });

                    City::resolveRelationUsing('jobs', function ($model) {
                        return $model->hasMany(Job::class, 'city_id');
                    });

                    $cities = City::query()
                        ->whereIn('id', $cityIds)
                        ->withCount(['companies', 'jobs' => function (Builder $query) {
                            $query
                                ->where(JobBoardHelper::getJobDisplayQueryConditions())
                                ->notExpired()
                                ->addApplied()
                                ->orderBy('is_featured', 'DESC')
                                ->latest();
                        }])
                        ->with(['country', 'metadata'])
                        ->take(6)
                        ->get();
                }

                if (! empty($stateIds)) {
                    State::resolveRelationUsing('companies', function ($model) {
                        return $model->hasMany(Company::class, 'state_id');
                    });

                    State::resolveRelationUsing('jobs', function ($model) {
                        return $model->hasMany(Job::class, 'state_id');
                    });

                    $states = State::query()
                        ->whereIn('id', $stateIds)
                        ->withCount(['companies', 'jobs' => function (Builder $query) {
                            $query
                                ->where(JobBoardHelper::getJobDisplayQueryConditions())
                                ->notExpired()
                                ->addApplied()
                                ->orderBy('is_featured', 'DESC')
                                ->latest();
                        }])
                        ->with(['country', 'metadata'])
                        ->take(6)
                        ->get();
                }

                $locations = $cities->merge($states);

                $title = $shortcode->title;
                $description = $shortcode->description;
                $style = $shortcode->style;

                return Theme::partial(
                    'shortcodes.job-by-location',
                    compact('title', 'description', 'style', 'states', 'locations')
                );
            });

            shortcode()->setAdminConfig('job-by-location', function (array $attributes) {
                $cities = City::query()
                    ->where('status', BaseStatusEnum::PUBLISHED)
                    ->pluck('name', 'id');

                $states = State::query()
                    ->where('status', BaseStatusEnum::PUBLISHED)
                    ->pluck('name', 'id');

                return Html::script('vendor/core/core/base/libraries/tagify/tagify.js') .
                    Html::script('vendor/core/core/base/js/tags.js') .
                    Theme::partial('shortcodes.job-by-location-admin-config', compact('attributes', 'cities', 'states'));
            });
        }

        add_shortcode('news-and-blogs', __('News and blog'), __('News and blog'), function (Shortcode $shortcode) {
            $posts = app(PostInterface::class)
                ->getFeatured(6, [
                    'slugable',
                    'tags',
                    'tags.slugable',
                    'metadata',
                    'author',
                ]);

            return Theme::partial('shortcodes.news-and-blogs', compact('shortcode', 'posts'));
        });

        shortcode()->setAdminConfig('news-and-blogs', function (array $attributes) {
            return Theme::partial('shortcodes.news-and-blogs-admin-config', compact('attributes'));
        });

        add_shortcode('pricing-table', __('Pricing Table'), __('Pricing Table'), function (Shortcode $shortcode) {
            $packages = app(PackageInterface::class)->advancedGet([
                'condition' => ['status' => BaseStatusEnum::PUBLISHED],
                'take' => (int)$shortcode->number_of_package ?: 6,
                'order_by' => ['created_at' => 'DESC'],
            ]);

            return Theme::partial('shortcodes.pricing-table', compact('shortcode', 'packages'));
        });

        shortcode()->setAdminConfig('pricing-table', function (array $attributes) {
            return Theme::partial('shortcodes.pricing-table-admin-config', compact('attributes'));
        });

        shortcode()->setAdminConfig('job-grid', function (array $attributes) {
            return Theme::partial('shortcodes.job-grid-admin-config', compact('attributes'));
        });

        add_shortcode('top-companies', __('Top companies table'), __('Top companies table'), function (Shortcode $shortcode) {
            $with = ['slugable'];

            if (is_plugin_active('location')) {
                $with = array_merge($with, array_keys(Location::getSupported(Job::class)));
            }

            $companies = app(CompanyInterface::class)
                ->advancedGet([
                    'with' => $with,
                    'condition' => [
                        'is_featured' => 1,
                    ],
                    'withCount' => [
                        'reviews',
                        'jobs' => function (Builder $query) {
                            $query
                                ->where(JobBoardHelper::getJobDisplayQueryConditions())
                                ->notExpired();
                        },
                    ],
                    'withAvg' => [
                        'reviews',
                        'star',
                    ],
                    'take' => 15,
                    'orderBy' => [
                        'created_at' => 'DESC',
                    ],
                ]);

            return Theme::partial('shortcodes.top-companies', compact('shortcode', 'companies'));
        });

        shortcode()->setAdminConfig('top-companies', function (array $attributes) {
            return Theme::partial('shortcodes.top-companies-admin-config', compact('attributes'));
        });

        add_shortcode('popular-category', __('Popular category'), __('Popular category'), function (Shortcode $shortcode) {
            $categories = app(CategoryInterface::class)
                ->getFeaturedCategories($shortcode->limit_category ?: 10);

            $categories->loadCount('activeJobs');

            return Theme::partial('shortcodes.popular-category', compact('shortcode', 'categories'));
        });

        shortcode()->setAdminConfig('popular-category', function (array $attributes) {
            return Theme::partial('shortcodes.popular-category-admin-config', compact('attributes'));
        });

        add_shortcode('advertisement-banner', __('Advertisement banner'), __('Advertisement banner'), function (Shortcode $shortcode) {
            return Theme::partial('shortcodes.advertisement-banner', compact('shortcode'));
        });

        shortcode()->setAdminConfig('advertisement-banner', function (array $attributes) {
            return Theme::partial('shortcodes.advertisement-banner-admin-config', compact('attributes'));
        });

        add_shortcode('counter-section', __('Counter section'), __('Counter section'), function (Shortcode $shortcode) {
            return Theme::partial('shortcodes.counter-section', compact('shortcode'));
        });

        shortcode()->setAdminConfig('counter-section', function (array $attributes) {
            return Theme::partial('shortcodes.counter-section-admin-config', compact('attributes'));
        });

        add_shortcode('our-partners', __('Box Trust'), __('Box Trust'), function (Shortcode $shortcode) {
            return Theme::partial('shortcodes.our-partners', compact('shortcode'));
        });

        shortcode()->setAdminConfig('our-partners', function (array $attributes) {
            return Theme::partial('shortcodes.our-partners-admin-config', compact('attributes'));
        });

        add_shortcode('top-candidates', __('Top Candidates'), __('Top Candidates'), function (Shortcode $shortcode) {
            if (JobBoardHelper::isDisabledPublicProfile()) {
                $candidates = collect();
            } else {
                $candidates = app(AccountInterface::class)
                    ->getModel()
                    ->with('slugable')
                    ->withCount('reviews')
                    ->withAvg('reviews', 'star')
                    ->where('is_featured', 1)
                    ->where('is_public_profile', 1)
                    ->where('type', AccountTypeEnum::JOB_SEEKER)
                    ->limit($shortcode->limit ?: 8)
                    ->latest()
                    ->get();
            }

            return Theme::partial('shortcodes.top-candidates', compact('shortcode', 'candidates'));
        });

        shortcode()->setAdminConfig('top-candidates', function (array $attributes) {
            return Theme::partial('shortcodes.top-candidates-admin-config', compact('attributes'));
        });

        add_shortcode('job-list', __('Job list'), __('Show job list'), function (Shortcode $shortcode) {
            $requestQuery = JobBoardHelper::getJobFilters(request()->input());

            $with = [
                'tags.slugable',
                'jobTypes',
                'slugable',
                'jobExperience',
                'company',
                'company.metadata',
                'company.slugable',
            ];

            $sortBy = match (request()->input('sort_by') ?: 'newest') {
                'oldest' => [
                    'jb_jobs.created_at' => 'ASC',
                    'jb_jobs.is_featured' => 'DESC',
                ],
                default => [
                    'jb_jobs.created_at' => 'DESC',
                    'jb_jobs.is_featured' => 'DESC',
                ],
            };

            if (is_plugin_active('location')) {
                $with = array_merge($with, array_keys(Location::getSupported(Job::class)));
            }

            $jobs = app(JobInterface::class)->getJobs(
                $requestQuery,
                [
                    'with' => $with,
                    'order_by' => $sortBy,
                    'paginate' => [
                        'per_page' => $requestQuery['per_page'] ?: Arr::first(JobBoardHelper::getPerPageParams()),
                        'current_paged' => $requestQuery['page'] ?: 1,
                    ],
                ],
            );

            $jobCategories = app(CategoryInterface::class)
                ->advancedGet(
                    [
                        'withCount' => [
                            'jobs' => function (Builder $query) {
                                $query
                                    ->where(JobBoardHelper::getJobDisplayQueryConditions())
                                    ->notExpired();
                            },
                        ],
                        'condition' => [
                            'status' => BaseStatusEnum::PUBLISHED,
                        ],
                    ]
                );

            $jobTypes = app(JobTypeInterface::class)
                ->advancedGet([
                    'withCount' => [
                        'jobs' => function (Builder $query) {
                            $query
                                ->where(JobBoardHelper::getJobDisplayQueryConditions())
                                ->notExpired();
                        },
                    ],
                    'condition' => [
                        'status' => BaseStatusEnum::PUBLISHED,
                    ],
                ]);

            $jobExperiences = app(JobExperienceInterface::class)
                ->advancedGet([
                    'withCount' => [
                        'jobs' => function (Builder $query) {
                            $query
                                ->where(JobBoardHelper::getJobDisplayQueryConditions())
                                ->notExpired();
                        },
                    ],
                    'condition' => [
                        'status' => BaseStatusEnum::PUBLISHED,
                    ],
                ]);

            $jobSkills = app(JobSkillInterface::class)
                ->advancedGet([
                    'withCount' => [
                        'jobs' => function (Builder $query) {
                            $query
                                ->where(JobBoardHelper::getJobDisplayQueryConditions())
                                ->notExpired();
                        },
                    ],
                    'condition' => [
                        'status' => BaseStatusEnum::PUBLISHED,
                    ],
                ]);

            return Theme::partial('shortcodes.job-list', compact(
                'shortcode',
                'jobs',
                'jobCategories',
                'jobTypes',
                'jobExperiences',
                'jobSkills'
            ));
        });

        shortcode()->setAdminConfig('job-list', function (array $attributes) {
            return Theme::partial('shortcodes.job-list-admin-config', compact('attributes'));
        });

        add_shortcode('job-companies', __('Company list'), __('Company list'), function (Shortcode $shortcode) {
            $requestQuery = JobBoardHelper::getCompanyFilterParams(request()->input());
            $condition = [];

            $sortBy = match ($requestQuery['sort_by'] ?? 'newest') {
                'oldest' => [
                    'jb_companies.is_featured' => 'ASC',
                    'jb_companies.created_at' => 'DESC',
                ],
                default => [
                    'jb_companies.created_at' => 'DESC',
                    'jb_companies.is_featured' => 'DESC',
                ],
            };

            if (! empty($requestQuery['keyword'])) {
                $condition['like'] = ['jb_companies.name', 'LIKE', $requestQuery['keyword'] . '%'];
            }

            $with = [
                'slugable',
                'metadata',
            ];

            if (is_plugin_active('location')) {
                $with = array_merge($with, array_keys(Location::getSupported(Company::class)));
            }

            $companies = app(CompanyInterface::class)
                ->advancedGet([
                    'withCount' => [
                        'jobs' => function (Builder $query) {
                            $query
                                ->where(JobBoardHelper::getJobDisplayQueryConditions())
                                ->notExpired();
                        },
                        'reviews',
                    ],
                    'condition' => $condition,
                    'order_by' => $sortBy,
                    'with' => $with,
                    'withAvg' => ['reviews', 'star'],
                    'paginate' => [
                        'per_page' => $requestQuery['per_page'] ?: Arr::first(JobBoardHelper::getPerPageParams()),
                        'current_paged' => $requestQuery['page'] ?: 1,
                    ],
                ]);

            return Theme::partial('shortcodes.job-companies', compact('shortcode', 'companies'));
        });

        shortcode()->setAdminConfig('job-companies', function (array $attributes) {
            return Theme::partial('shortcodes.job-companies-admin-config', compact('attributes'));
        });
    }

    if (is_plugin_active('contact')) {
        add_filter(CONTACT_FORM_TEMPLATE_VIEW, function () {
            return Theme::getThemeNamespace('partials.shortcodes.contact-form');
        }, 99);

        shortcode()->setAdminConfig('contact-form', function (array $attributes) {
            return Theme::partial('shortcodes.contact-admin-config', compact('attributes'));
        });
    }

    if (is_plugin_active('team')) {
        add_shortcode('team', __('Team'), __('Team'), function (Shortcode $shortcode) {
            $teams = app(TeamInterface::class)->advancedGet([
                'condition' => ['status' => BaseStatusEnum::PUBLISHED],
                'take' => (int)$shortcode->number_of_people ?: 6,
                'order_by' => ['created_at' => 'DESC'],
            ]);

            return Theme::partial('shortcodes.team', compact('shortcode', 'teams'));
        });

        shortcode()->setAdminConfig('team', function (array $attributes) {
            return Theme::partial('shortcodes.team-admin-config', compact('attributes'));
        });
    }

    if (is_plugin_active('faq')) {
        add_shortcode('faq', __('FAQ'), __('FAQ'), function (Shortcode $shortcode) {
            if ($categoryIds = $shortcode->category_ids) {
                $categoryIds = explode(',', $categoryIds);

                if (count($categoryIds) > 0) {
                    $faqCategories = app(FaqCategoryInterface::class)->advancedGet([
                        'condition' => [
                            'status' => BaseStatusEnum::PUBLISHED,
                            'IN' => ['id', 'IN', $categoryIds],
                        ],
                        'order_by' => ['created_at' => 'DESC'],
                    ]);
                } else {
                    $faqCategories = collect();
                }

                $faqs = collect();
            } else {
                $faqs = app(FaqInterface::class)->advancedGet([
                    'condition' => ['status' => BaseStatusEnum::PUBLISHED],
                    'take' => (int)$shortcode->number_of_faq ?: 6,
                    'order_by' => ['created_at' => 'DESC'],
                ]);

                $faqCategories = collect();
            }

            return Theme::partial('shortcodes.faq', compact('shortcode', 'faqCategories', 'faqs'));
        });

        shortcode()->setAdminConfig('faq', function (array $attributes) {
            $categories = app(FaqCategoryInterface::class)->advancedGet([
                'condition' => ['status' => BaseStatusEnum::PUBLISHED],
            ]);

            return Theme::partial('shortcodes.faq-admin-config', compact('attributes', 'categories'));
        });
    }

    add_shortcode('gallery', __('Gallery'), __('Gallery'), function (Shortcode $shortcode) {
        return Theme::partial('shortcodes.gallery', compact('shortcode'));
    });

    shortcode()->setAdminConfig('gallery', function (array $attributes) {
        return Theme::partial('shortcodes.gallery-admin-config', compact('attributes'));
    });

    add_shortcode('job-search-banner', __('Job search banner'), __('Job search banner'), function (Shortcode $shortcode) {
        return Theme::partial('shortcodes.job-search-banner', compact('shortcode'));
    });

    shortcode()->setAdminConfig('job-search-banner', function (array $attributes) {
        return Theme::partial('shortcodes.job-search-banner-admin-config', compact('attributes'));
    });

    add_shortcode('how-it-works', __('How It Works'), __('How It Works'), function (Shortcode $shortcode) {
        return Theme::partial('shortcodes.how-it-works', compact('shortcode'));
    });

    shortcode()->setAdminConfig('how-it-works', function (array $attributes) {
        return Theme::partial('shortcodes.how-it-works-admin-config', compact('attributes'));
    });

    add_shortcode('job-candidates', __('Job Candidates'), __('Job Candidates'), function (Shortcode $shortcode) {
        $candidates = new LengthAwarePaginator(collect(), 0, Arr::first(JobBoardHelper::getPerPageParams()));
        if (! JobBoardHelper::isDisabledPublicProfile()) {
            $candidates = JobBoardHelper::filterCandidates(request()->input());
        }

        return Theme::partial('shortcodes.job-candidates', compact('shortcode', 'candidates'));
    });

    shortcode()->setAdminConfig('job-candidates', function (array $attributes) {
        return Theme::partial('shortcodes.job-candidates-admin-config', compact('attributes'));
    });

    if (is_plugin_active('testimonial')) {
        add_shortcode('testimonials', __('Testimonials'), __('Testimonials'), function (Shortcode $shortcode) {
            $testimonials = Testimonial::query()
                ->where('status', BaseStatusEnum::PUBLISHED)
                ->get();

            return Theme::partial('shortcodes.testimonials', compact('shortcode', 'testimonials'));
        });

        shortcode()->setAdminConfig('testimonials', function (array $attributes) {
            return Theme::partial('shortcodes.testimonials-admin-config', compact('attributes'));
        });
    }
});

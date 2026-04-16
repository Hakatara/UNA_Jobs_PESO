<?php

namespace Botble\JobBoard\Listeners;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\JobBoard\Facades\JobBoardHelper;
use Botble\JobBoard\Repositories\Interfaces\CategoryInterface;
use Botble\JobBoard\Repositories\Interfaces\JobInterface;
use Botble\JobBoard\Repositories\Interfaces\TagInterface;
use Botble\Theme\Events\RenderingSiteMapEvent;
use Botble\Theme\Facades\SiteMapManager;
use Illuminate\Support\Arr;

class RenderingSiteMapListener
{
    public function __construct(
        protected JobInterface $jobRepository,
        protected CategoryInterface $categoryRepository,
        protected TagInterface $tagRepository
    ) {
    }

    public function handle(RenderingSiteMapEvent $event): void
    {
        if ($key = $event->key) {
            switch ($key) {
                case 'job-categories':

                    $categories = $this->categoryRepository->getModel()
                        ->where('status', BaseStatusEnum::PUBLISHED)
                        ->latest('updated_at')
                        ->with(['slugable'])
                        ->get();

                    foreach ($categories as $category) {
                        SiteMapManager::add($category->url, $category->updated_at, '0.8');
                    }

                    break;

                case 'job-tags':

                    $tags = $this->tagRepository->getModel()
                        ->where('status', BaseStatusEnum::PUBLISHED)
                        ->latest('updated_at')
                        ->with(['slugable'])
                        ->get();

                    foreach ($tags as $tag) {
                        SiteMapManager::add($tag->url, $tag->updated_at, '0.4');
                    }

                    break;
            }

            if (preg_match('/^jobs-((?:19|20|21|22)\d{2})-(0?[1-9]|1[012])$/', $key, $matches)) {
                if (($year = Arr::get($matches, 1)) && ($month = Arr::get($matches, 2))) {
                    $jobs = $this->jobRepository->getModel()
                        ->notExpired()
                        ->where(JobBoardHelper::getJobDisplayQueryConditions())
                        ->whereYear('updated_at', $year)
                        ->whereMonth('updated_at', $month)
                        ->latest('updated_at')
                        ->select(['id', 'name', 'updated_at'])
                        ->with(['slugable'])
                        ->get();

                    foreach ($jobs as $job) {
                        if (! $job->slugable) {
                            continue;
                        }

                        SiteMapManager::add($job->url, $job->updated_at, '0.8');
                    }
                }
            }

            return;
        }

        $jobs = $this->jobRepository->getModel()
            ->selectRaw('YEAR(updated_at) as updated_year, MONTH(updated_at) as updated_month, MAX(updated_at) as updated_at')
            ->notExpired()
            ->where(JobBoardHelper::getJobDisplayQueryConditions())
            ->groupBy('updated_year', 'updated_month')
            ->orderBy('updated_year', 'desc')
            ->orderBy('updated_month', 'desc')
            ->get();

        foreach ($jobs as $job) {
            $key = sprintf('jobs-%s-%s', $job->updated_year, str_pad($job->updated_month, 2, '0', STR_PAD_LEFT));
            SiteMapManager::addSitemap(SiteMapManager::route($key), $job->updated_at);
        }

        $categoryLastUpdated = $this->categoryRepository
            ->getModel()
            ->where('status', BaseStatusEnum::PUBLISHED)
            ->latest('updated_at')
            ->value('updated_at');

        SiteMapManager::addSitemap(SiteMapManager::route('job-categories'), $categoryLastUpdated);

        $tagLastUpdated = $this->tagRepository
            ->getModel()
            ->where('status', BaseStatusEnum::PUBLISHED)
            ->latest('updated_at')
            ->value('updated_at');

        SiteMapManager::addSitemap(SiteMapManager::route('job-tags'), $tagLastUpdated);
    }
}

<?php

use Botble\Base\Enums\BaseStatusEnum;
use Botble\JobBoard\Repositories\Interfaces\CategoryInterface;
use Botble\JobBoard\Repositories\Interfaces\JobInterface;

if (! function_exists('get_latest_jobs')) {
    /**
     * @param int $limit
     * @return mixed
     */
    function get_latest_jobs(int $limit = 10)
    {
        $with = ['slugable'];

        if (is_plugin_active('location')) {
            $with = array_merge($with, ['city', 'state']);
        }

        return app(JobInterface::class)
            ->getModel()
            ->notExpired()
            ->where(JobBoardHelper::getJobDisplayQueryConditions())
            ->orderBy('jb_jobs.created_at', 'DESC')
            ->with($with)
            ->take($limit)
            ->get();
    }
}

if (! function_exists('count_active_jobs')) {
    /**
     * @return mixed
     */
    function count_active_jobs()
    {
        return app(JobInterface::class)->countActiveJobs();
    }
}

if (! function_exists('get_job_categories')) {
    /**
     * @param int $limit
     * @return mixed
     */
    function get_job_categories(int $limit = 10)
    {
        return app(CategoryInterface::class)
            ->getModel()
            ->where('status', BaseStatusEnum::PUBLISHED)
            ->orderBy('order', 'DESC')
            ->orderBy('created_at', 'DESC')
            /*->withCount(['jobs' => function ($query) {
                $query->where('jb_jobs.status', BaseStatusEnum::PUBLISHED)
                    ->notExpired();
            }])*/
            ->take($limit)
            ->with(['slugable'])
            ->get();
    }
}

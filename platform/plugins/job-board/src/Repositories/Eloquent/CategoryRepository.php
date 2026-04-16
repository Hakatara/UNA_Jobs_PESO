<?php

namespace Botble\JobBoard\Repositories\Eloquent;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\JobBoard\Repositories\Interfaces\CategoryInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;

class CategoryRepository extends RepositoriesAbstract implements CategoryInterface
{
    public function getFeaturedCategories(int $limit = 8, array $with = [])
    {
        $with = array_merge($with, ['slugable', 'metadata']);
        $data = $this->model
            ->where(['status' => BaseStatusEnum::PUBLISHED, 'is_featured' => true])
            ->orderBy('order', 'ASC')
            ->orderBy('created_at', 'DESC')
            ->withCount(['activeJobs'])
            ->limit($limit)
            ->with($with);

        return $this->applyBeforeExecuteQuery($data)->get();
    }

    public function getCategories(array $with = [])
    {
        $with = array_merge($with, ['slugable']);
        $data = $this->model
            ->where(['status' => BaseStatusEnum::PUBLISHED])
            ->orderBy('order', 'ASC')
            ->orderBy('created_at', 'DESC')
            ->withCount(['activeJobs'])
            ->with($with);

        return $this->applyBeforeExecuteQuery($data)->get();
    }
}

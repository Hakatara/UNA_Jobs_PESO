<?php

namespace Botble\JobBoard\Repositories\Interfaces;

use Botble\Support\Repositories\Interfaces\RepositoryInterface;

interface CategoryInterface extends RepositoryInterface
{
    /**
     * @param int $limit
     * @param array $with
     * @return mixed
     */
    public function getFeaturedCategories(int $limit = 8, array $with = []);

    /**
     * @param array $with
     * @return mixed
     */
    public function getCategories(array $with = []);
}

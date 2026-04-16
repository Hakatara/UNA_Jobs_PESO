<?php

namespace Botble\Team\Repositories\Eloquent;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;
use Botble\Team\Repositories\Interfaces\TeamInterface;

class TeamRepository extends RepositoriesAbstract implements TeamInterface
{
    public function getTeams(int $limit = 8, bool $active = true)
    {
        $data = $this->model;
        if ($active) {
            $data = $data->where('status', BaseStatusEnum::PUBLISHED);
        }
        if ($limit) {
            $data = $data->limit($limit);
        }

        return $this->applyBeforeExecuteQuery($data)->get();
    }
}

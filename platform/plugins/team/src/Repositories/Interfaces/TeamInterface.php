<?php

namespace Botble\Team\Repositories\Interfaces;

use Botble\Support\Repositories\Interfaces\RepositoryInterface;

interface TeamInterface extends RepositoryInterface
{
    public function getTeams(int $limit = 8, bool $active = true);
}

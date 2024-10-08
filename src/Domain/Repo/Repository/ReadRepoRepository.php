<?php

namespace App\Domain\Repo\Repository;

use App\Domain\Repo\Repo;

interface ReadRepoRepository
{
    public function exist(Repo $repo): bool;
}

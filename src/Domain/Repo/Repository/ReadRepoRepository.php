<?php

declare(strict_types=1);

namespace App\Domain\Repo\Repository;

use App\Domain\Repo\Repo;

interface ReadRepoRepository
{
    public function exist(Repo $repo): bool;
}

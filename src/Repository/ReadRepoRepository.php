<?php

namespace App\Repository;

use App\Entity\Repo;

interface ReadRepoRepository
{
    public function exist(Repo $repo): bool;
}

<?php

namespace App\Domain\Repo\Repository;

interface WriteRepoRepository
{
    public function insertBatch(array $repos): void;
}

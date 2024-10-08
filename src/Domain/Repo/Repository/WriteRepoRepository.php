<?php

declare(strict_types=1);

namespace App\Domain\Repo\Repository;

interface WriteRepoRepository
{
    public function insertBatch(array $repos): void;
}

<?php

namespace App\Infrastructure\ORM\Repo\Repository;

use App\Domain\Repo\Repo;
use App\Domain\Repo\Repository\ReadRepoRepository;
use App\Domain\Repo\Repository\WriteRepoRepository;
use Doctrine\DBAL\Connection;

class DbalWriteRepoRepository implements WriteRepoRepository
{
    public function __construct(
        private readonly Connection $connection,
        private readonly ReadRepoRepository $readRepoRepository
    ) {
    }

    /**
     * @param Repo[] $repos
     * @return void
     */
    public function insertBatch(array $repos): void {
        $reposToMigrate = [];

        foreach ($repos as $repo) {
            if (!$this->readRepoRepository->exist($repo)) {
                $reposToMigrate[] = sprintf("(%d, '%s', '%s')", $repo->getId(), $repo->getName(), $repo->getUrl());
            }
        }

        if (!empty($reposToMigrate)) {
            $query = "INSERT INTO repo (id, name, url) VALUES " . implode(',', $reposToMigrate);
            $this->connection->executeQuery($query);
        }
    }
}

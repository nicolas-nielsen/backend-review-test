<?php

namespace App\Repository;

use App\Entity\Repo;
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
                $reposToMigrate[] = sprintf("(%d, '%s', '%s')", $repo->id(), $repo->name(), $repo->url());
            }
        }

        if (!empty($reposToMigrate)) {
            $query = "INSERT INTO repo (id, name, url) VALUES " . implode(',', $reposToMigrate);
            $this->connection->executeQuery($query);
        }
    }
}

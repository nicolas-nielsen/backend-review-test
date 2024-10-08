<?php

namespace App\Repository;

use App\Entity\Actor;
use Doctrine\DBAL\Connection;

class DbalWriteActorRepository implements WriteActorRepository
{
    public function __construct(
        private readonly Connection $connection,
        private readonly ReadActorRepository $readActorRepository
    ) {
    }

    /**
     * @param Actor[] $actors
     * @return void
     */
    public function insertBatch(array $actors): void {
        $actorsToMigrate = [];

        foreach ($actors as $actor) {
            if (!$this->readActorRepository->exist($actor)) {
                $actorsToMigrate[] = sprintf("(%d, '%s', '%s', '%s')", $actor->getId(), $actor->getLogin(), $actor->getUrl(), $actor->getAvatarUrl());
            }
        }

        if (!empty($actorsToMigrate)) {
            $query = "INSERT INTO actor (id, login, url, avatar_url) VALUES " . implode(',', $actorsToMigrate);
            $this->connection->executeQuery($query);
        }
    }
}

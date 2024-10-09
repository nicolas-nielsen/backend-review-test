<?php

declare(strict_types=1);

namespace App\Infrastructure\ORM\Actor\Repository;

use App\Domain\Actor\Actor;
use App\Domain\Actor\Repository\ReadActorRepository;
use App\Domain\Actor\Repository\WriteActorRepository;
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
     */
    public function insertBatch(array $actors): void
    {
        $actorsToMigrate = [];

        foreach ($actors as $actor) {
            if (!$this->readActorRepository->exist($actor)) {
                $actorsToMigrate[] = sprintf("(%d, '%s', '%s', '%s')", $actor->getId(), $actor->getLogin(), $actor->getUrl(), $actor->getAvatarUrl());
            }
        }

        if (!empty($actorsToMigrate)) {
            $query = 'INSERT INTO actor (id, login, url, avatar_url) VALUES '.implode(',', $actorsToMigrate);
            $this->connection->executeQuery($query);
        }
    }
}

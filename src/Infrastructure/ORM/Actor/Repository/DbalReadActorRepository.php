<?php

namespace App\Infrastructure\ORM\Actor\Repository;

use App\Domain\Actor\Actor;
use App\Domain\Actor\Repository\ReadActorRepository;
use Doctrine\DBAL\Connection;

class DbalReadActorRepository implements ReadActorRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function exist(Actor $actor): bool
    {
        $sql = <<<SQL
            SELECT 1
            FROM actor
            WHERE id = :id
        SQL;

        $result = $this->connection->fetchOne($sql, [
            'id' => $actor->getId()
        ]);

        return (bool) $result;
    }
}

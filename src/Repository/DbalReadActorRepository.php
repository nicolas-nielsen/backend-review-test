<?php

namespace App\Repository;

use App\Entity\Actor;
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
            'id' => $actor->id()
        ]);

        return (bool) $result;
    }
}

<?php

namespace App\Repository;

use App\Entity\Repo;
use Doctrine\DBAL\Connection;

class DbalReadRepoRepository implements ReadRepoRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function exist(Repo $repo): bool
    {
        $sql = <<<SQL
            SELECT 1
            FROM repo
            WHERE id = :id
        SQL;

        $result = $this->connection->fetchOne($sql, [
            'id' => $repo->getId()
        ]);

        return (bool) $result;
    }
}

<?php

namespace App\Repository;

use App\Dto\SearchInput;
use App\Entity\EventType;
use Doctrine\DBAL\Connection;

class DbalReadEventRepository implements ReadEventRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function countAll(SearchInput $searchInput): int
    {
        $sql = <<<SQL
        SELECT sum(count) as count
        FROM event
        WHERE date(create_at) = :date
        AND payload::text like :keyword
SQL;

        return (int) $this->connection->fetchOne($sql, [
            'date' => $searchInput->date->format(DATE_ATOM),
            'keyword' => '%'.$searchInput->keyword.'%'
        ]);
    }

    public function countByType(SearchInput $searchInput): array
    {
        $sql = <<<'SQL'
            SELECT type, sum(count) as count
            FROM event
            WHERE date(create_at) = :date
            AND payload::text like :keyword
            GROUP BY type
SQL;

        return $this->connection->fetchAllKeyValue($sql, [
            'date' => $searchInput->date->format(DATE_ATOM),
            'keyword' => '%'.$searchInput->keyword.'%'
        ]);
    }

    public function statsByTypePerHour(SearchInput $searchInput): array
    {
        $sql = <<<SQL
            SELECT extract(hour from create_at) as hour, type, sum(count) as count
            FROM event
            WHERE date(create_at) = :date
            AND payload::text like :keyword
            GROUP BY TYPE, EXTRACT(hour from create_at)
SQL;

        $stats = $this->connection->fetchAllAssociative($sql, [
            'date' => $searchInput->date->format(DATE_ATOM),
            'keyword' => '%'.$searchInput->keyword.'%'
        ]);

        $data = array_fill(0, 24, ['Commit' => 0, 'Pull Request' => 0, 'Comment' => 0]);

        foreach ($stats as $stat) {
            $data[(int) $stat['hour']][array_flip(EventType::getChoices())[$stat['type']]] = $stat['count'];
        }

        return $data;
    }

    public function getLatest(SearchInput $searchInput): array
    {
        $sql = <<<SQL
            SELECT type, to_json(repo) as repo
            FROM event
            LEFT JOIN repo on event.repo_id = repo.id
            WHERE date(create_at) = :date
            AND payload::text like :keyword
SQL;

        $result = $this->connection->fetchAllAssociative($sql, [
            'date' => $searchInput->date->format(DATE_ATOM),
            'keyword' => '%'.$searchInput->keyword.'%'
        ]);

        $result = array_map(static function($item) {
            $item['repo'] = json_decode($item['repo'], true);

            return $item;
        }, $result);

        return $result;
    }

    public function exist(int $id): bool
    {
        $sql = <<<SQL
            SELECT 1
            FROM event
            WHERE id = :id
        SQL;

        $result = $this->connection->fetchOne($sql, [
            'id' => $id
        ]);

        return (bool) $result;
    }
}

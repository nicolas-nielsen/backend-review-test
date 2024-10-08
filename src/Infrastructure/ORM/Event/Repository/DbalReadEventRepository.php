<?php

declare(strict_types=1);

namespace App\Infrastructure\ORM\Event\Repository;

use App\Domain\Event\Data\SearchEventFilter;
use App\Domain\Event\EventType;
use App\Domain\Event\Repository\ReadEventRepository;
use Doctrine\DBAL\Connection;

class DbalReadEventRepository implements ReadEventRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function countAll(SearchEventFilter $searchEventFilter): int
    {
        $sql = <<<SQL
        SELECT sum(count) as count
        FROM event
        WHERE date(created_at) = :date
        AND payload::text like :keyword
SQL;

        return (int) $this->connection->fetchOne($sql, [
            'date' => $searchEventFilter->getDate()->format(DATE_ATOM),
            'keyword' => '%'.$searchEventFilter->getKeyword().'%',
        ]);
    }

    public function countByType(SearchEventFilter $searchEventFilter): array
    {
        $sql = <<<'SQL'
            SELECT type, sum(count) as count
            FROM event
            WHERE date(created_at) = :date
            AND payload::text like :keyword
            GROUP BY type
SQL;

        return $this->connection->fetchAllKeyValue($sql, [
            'date' => $searchEventFilter->getDate()->format(DATE_ATOM),
            'keyword' => '%'.$searchEventFilter->getKeyword().'%',
        ]);
    }

    public function statsByTypePerHour(SearchEventFilter $searchEventFilter): array
    {
        $sql = <<<SQL
            SELECT extract(hour from created_at) as hour, type, sum(count) as count
            FROM event
            WHERE date(created_at) = :date
            AND payload::text like :keyword
            GROUP BY TYPE, EXTRACT(hour from created_at)
SQL;

        $stats = $this->connection->fetchAllAssociative($sql, [
            'date' => $searchEventFilter->getDate()->format(DATE_ATOM),
            'keyword' => '%'.$searchEventFilter->getKeyword().'%',
        ]);

        $data = array_fill(0, 24, ['Commit' => 0, 'Pull Request' => 0, 'Comment' => 0]);

        foreach ($stats as $stat) {
            $data[(int) $stat['hour']][array_flip(EventType::getChoices())[$stat['type']]] = $stat['count'];
        }

        return $data;
    }

    public function getLatest(SearchEventFilter $searchEventFilter): array
    {
        $sql = <<<SQL
            SELECT type, to_json(repo) as repo
            FROM event
            LEFT JOIN repo on event.repo_id = repo.id
            WHERE date(created_at) = :date
            AND payload::text like :keyword
SQL;

        $result = $this->connection->fetchAllAssociative($sql, [
            'date' => $searchEventFilter->getDate()->format(DATE_ATOM),
            'keyword' => '%'.$searchEventFilter->getKeyword().'%',
        ]);

        $result = array_map(static function ($item) {
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
            'id' => $id,
        ]);

        return (bool) $result;
    }
}

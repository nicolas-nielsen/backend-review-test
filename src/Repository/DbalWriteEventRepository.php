<?php

namespace App\Repository;

use App\Dto\EventInput;
use App\Entity\Event;
use Doctrine\DBAL\Connection;

class DbalWriteEventRepository implements WriteEventRepository
{
    public function __construct(
        private readonly Connection $connection,
        private readonly ReadEventRepository $readEventRepository,
    ) {
    }

    public function update(EventInput $authorInput, int $id): void
    {
        $sql = <<<SQL
        UPDATE event
        SET comment = :comment
        WHERE id = :id
SQL;

        $this->connection->executeQuery($sql, ['id' => $id, 'comment' => $authorInput->comment]);
    }

    /**
     * @param Event[] $events
     * @return void
     */
    public function insertBatch(array $events): void
    {
        $eventsToMigrate = [];
        foreach ($events as $event) {
            if (!$this->readEventRepository->exist($event->getId())) {
                $eventsToMigrate[] = sprintf(
                    "(%d, %d, %d, '%s', %d, '%s', '%s', '%s')",
                    $event->getId(),
                    $event->getActor()->getId(),
                    $event->getRepo()->getId(),
                    $event->getType(),
                    $event->getCount(),
                    json_encode($event->getPayload(), JSON_HEX_APOS),
                    $event->getCreatedAt()->format(DATE_ATOM),
                    null
                );
            }
        }

        if (!empty($eventsToMigrate)) {
            $query = "INSERT INTO event (id, actor_id, repo_id, type, count, payload, create_at, comment) VALUES " . implode(',', $eventsToMigrate);
            $this->connection->executeQuery($query);
        }
    }
}

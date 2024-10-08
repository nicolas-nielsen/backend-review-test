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
            if (!$this->readEventRepository->exist($event->id())) {
                $eventsToMigrate[] = sprintf(
                    "(%d, %d, %d, '%s', %d, '%s', '%s', '%s')",
                    $event->id(),
                    $event->actor()->id(),
                    $event->repo()->id(),
                    $event->type(),
                    $event->count(),
                    json_encode($event->payload(), JSON_HEX_APOS),
                    $event->createAt()->format(DATE_ATOM),
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

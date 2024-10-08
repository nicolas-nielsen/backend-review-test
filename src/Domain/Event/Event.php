<?php

declare(strict_types=1);

namespace App\Domain\Event;

use App\Domain\Actor\Actor;
use App\Domain\Repo\Repo;

class Event
{
    private string $type;

    private int $count = 1;

    public function __construct(
        private int $id,
        string $type,
        private Actor $actor,
        private Repo $repo,
        private array $payload,
        private \DateTimeImmutable $createdAt,
        private ?string $comment
    )
    {
        EventType::assertValidChoice($type);
        $this->type = $type;

        if (EventType::COMMIT === $type) {
            $this->count = $payload['size'] ?? 1;
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getActor(): Actor
    {
        return $this->actor;
    }

    public function getRepo(): Repo
    {
        return $this->repo;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }
}

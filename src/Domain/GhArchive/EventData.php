<?php

declare(strict_types=1);

namespace App\Domain\GhArchive;

class EventData
{
    public int $id;
    public string $type;
    public string $created_at;
    public ActorData $actor;
    public RepoData $repo;
    public array $payload;

    public function setId(string $id): void
    {
        $this->id = (int) $id;
    }
}

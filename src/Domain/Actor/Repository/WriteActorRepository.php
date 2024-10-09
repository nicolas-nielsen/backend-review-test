<?php

declare(strict_types=1);

namespace App\Domain\Actor\Repository;

interface WriteActorRepository
{
    public function insertBatch(array $actors): void;
}

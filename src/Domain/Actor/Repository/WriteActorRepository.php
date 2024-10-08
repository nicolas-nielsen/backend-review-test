<?php

namespace App\Domain\Actor\Repository;

interface WriteActorRepository
{
    public function insertBatch(array $actors): void;
}

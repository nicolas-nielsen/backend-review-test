<?php

namespace App\Repository;

interface WriteActorRepository
{
    public function insertBatch(array $actors): void;
}

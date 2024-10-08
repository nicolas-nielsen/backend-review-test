<?php

namespace App\Repository;

interface WriteRepoRepository
{
    public function insertBatch(array $repos): void;
}

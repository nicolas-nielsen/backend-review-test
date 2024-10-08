<?php

namespace App\Domain\Actor\Repository;

use App\Domain\Actor\Actor;

interface ReadActorRepository
{
    public function exist(Actor $actor): bool;
}

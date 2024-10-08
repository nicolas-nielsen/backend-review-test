<?php

declare(strict_types=1);

namespace App\Domain\Actor\Repository;

use App\Domain\Actor\Actor;

interface ReadActorRepository
{
    public function exist(Actor $actor): bool;
}

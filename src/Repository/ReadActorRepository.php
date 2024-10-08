<?php

namespace App\Repository;

use App\Entity\Actor;

interface ReadActorRepository
{
    public function exist(Actor $actor): bool;
}

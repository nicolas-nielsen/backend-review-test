<?php

namespace App\Domain\Event\Repository;

use App\Domain\Event\Data\UpdateCommentData;

interface WriteEventRepository
{
    public function update(UpdateCommentData $updateCommentData, int $id): void;
    public function insertBatch(array $events): void;
}

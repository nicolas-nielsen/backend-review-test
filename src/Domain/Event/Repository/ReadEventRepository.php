<?php

declare(strict_types=1);

namespace App\Domain\Event\Repository;

use App\Domain\Event\Data\SearchEventFilter;

interface ReadEventRepository
{
    public function countAll(SearchEventFilter $searchEventFilter): int;

    public function countByType(SearchEventFilter $searchEventFilter): array;

    public function statsByTypePerHour(SearchEventFilter $searchEventFilter): array;

    public function getLatest(SearchEventFilter $searchEventFilter): array;

    public function exist(int $id): bool;
}

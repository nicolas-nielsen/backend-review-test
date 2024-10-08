<?php

declare(strict_types=1);

namespace App\Domain\Event\Data;

class SearchEventFilter
{
    public function __construct(private readonly \DateTimeImmutable $date, private readonly string $keyword)
    {
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getKeyword(): string
    {
        return $this->keyword;
    }
}

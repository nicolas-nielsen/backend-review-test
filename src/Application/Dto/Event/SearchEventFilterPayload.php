<?php

declare(strict_types=1);

namespace App\Application\Dto\Event;

use App\Domain\Event\Data\SearchEventFilter;

class SearchEventFilterPayload
{
    /**
     * @var \DateTimeImmutable
     */
    public $date;

    /**
     * @var string
     */
    public $keyword;

    public function createSearchEventFilter(): SearchEventFilter
    {
        return new SearchEventFilter($this->date, $this->keyword);
    }
}

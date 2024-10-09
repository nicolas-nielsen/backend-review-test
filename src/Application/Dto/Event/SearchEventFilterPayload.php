<?php

declare(strict_types=1);

namespace App\Application\Dto\Event;

use App\Domain\Event\Data\SearchEventFilter;
use Symfony\Component\Validator\Constraints as Assert;

class SearchEventFilterPayload
{
    #[Assert\DateTime]
    #[Assert\NotBlank]
    public \DateTimeImmutable $date;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $keyword;

    public function createSearchEventFilter(): SearchEventFilter
    {
        return new SearchEventFilter($this->date, $this->keyword);
    }
}

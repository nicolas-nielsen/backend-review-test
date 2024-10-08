<?php

declare(strict_types=1);

namespace App\Domain\Event\Data;

class UpdateCommentData
{
    public function __construct(private readonly string $comment)
    {
    }

    public function getComment(): string
    {
        return $this->comment;
    }
}

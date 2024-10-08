<?php

namespace App\Application\Dto\Event;

use App\Domain\Event\Data\UpdateCommentData;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateCommentPayload
{
    #[Assert\Length(min: 20)]
    #[Assert\NotBlank]
    public ?string $comment;

    public function __construct(?string $comment) {
        $this->comment = $comment;
    }

    public function createUpdateCommentData(): UpdateCommentData
    {
        return new UpdateCommentData($this->comment);
    }
}

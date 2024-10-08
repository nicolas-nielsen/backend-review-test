<?php

namespace App\Domain\Event;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class EventType extends AbstractEnumType
{
    public const COMMIT = 'COM';
    public const COMMENT = 'MSG';
    public const PULL_REQUEST = 'PR';

    protected static array $choices = [
        self::COMMIT => 'Commit',
        self::COMMENT => 'Comment',
        self::PULL_REQUEST => 'Pull Request',
    ];
}

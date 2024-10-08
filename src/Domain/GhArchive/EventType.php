<?php

namespace App\Domain\GhArchive;

enum EventType: string
{
    case COMMENT = 'MSG';
    case COMMIT = 'COM';
    case PULL_REQUEST = 'PR';
    case UNHANDLED = 'Unhandled event';

    public static function createFromGhArchiveEvent(string $eventName): self
    {
        return match ($eventName) {
            'CommitCommentEvent', 'IssueCommentEvent', 'PullRequestReviewComment' => self::COMMENT,
            'PushEvent' => self::COMMIT,
            'PullRequestEvent' => self::PULL_REQUEST,
            default => self::UNHANDLED
        };
    }
}

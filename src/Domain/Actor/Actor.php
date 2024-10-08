<?php

declare(strict_types=1);

namespace App\Domain\Actor;

class Actor
{
    public function __construct(
        private int $id,
        private string $login,
        private string $url,
        private string $avatarUrl
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getAvatarUrl(): string
    {
        return $this->avatarUrl;
    }
}

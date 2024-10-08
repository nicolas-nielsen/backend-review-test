<?php

namespace App\Tests\Unit;

use App\Infrastructure\Provider\GhArchiveProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\Serializer\SerializerInterface;

class GhArchiveProviderTest extends TestCase
{
    private const URL = 'https://data.gharchive.org/';
    private SerializerInterface $serializer;
    private LoggerInterface $logger;

    public function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testFakeUrlFileNotFound(): void
    {
        $ghArchiveProvider = new GhArchiveProvider('fakeUrl', $this->serializer, $this->logger);

        $date = '2024-10-02-12';
        $ghArchiveProvider->fetchEvents($date);
        $this->expectException(FileNotFoundException::class);
    }

    public function testFileNotFoundOnGha(): void
    {
        $ghArchiveProvider = new GhArchiveProvider(self::URL, $this->serializer, $this->logger);

        $date = '2025-10-02-12';
        $ghArchiveProvider->fetchEvents($date);
        $this->expectException(FileNotFoundException::class);
    }

    public function testIsIterable(): void
    {
        $ghArchiveProvider = new GhArchiveProvider(self::URL, $this->serializer, $this->logger);

        $date = '2023-01-01-12';

        $events = $ghArchiveProvider->fetchEvents($date);

        $this->assertIsIterable($events);
    }

}

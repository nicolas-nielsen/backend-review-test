<?php

namespace App\Infrastructure\Provider;

use App\Domain\GhArchive\EventData;
use App\Domain\GhArchive\EventType;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\Serializer\SerializerInterface;

class GhArchiveProvider
{
    private const EXTENSION = '.json.gz';

    public function __construct(
        private readonly string $url,
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger
    ) {
    }

    public function fetchEvents(string $date): \Generator
    {
        $file = $this->url . $date . self::EXTENSION;

        try {
            $stream = gzopen($file, 'r');
        } catch (\Exception $e) {
            throw new FileNotFoundException($file);
        }

        while (!gzeof($stream)) {
            try {
                $line = gzgets($stream);
                /** @var EventData $eventData */
                $eventData = $this->serializer->deserialize($line, EventData::class, 'json');
            } catch (\Exception $e) {
                $this->logger->info('Data from github archive does not contain expected types', ['data' => $line]);
                continue;
            }

            $eventType = EventType::createFromGhArchiveEvent($eventData->type);
            if ($eventType === EventType::PULL_REQUEST && $eventData->payload['action'] !== 'opened') {
                continue;
            }

            if ($eventType !== EventType::UNHANDLED) {
                yield $eventData;
            }
        }

        gzclose($stream);
    }
}

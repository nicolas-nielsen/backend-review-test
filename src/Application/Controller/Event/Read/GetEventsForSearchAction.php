<?php

declare(strict_types=1);

namespace App\Application\Controller\Event\Read;

use App\Application\Dto\Event\SearchEventFilterPayload;
use App\Domain\Event\EventType;
use App\Domain\Event\Repository\ReadEventRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class GetEventsForSearchAction
{
    private ReadEventRepository $repository;
    private SerializerInterface $serializer;

    public function __construct(
        ReadEventRepository $repository,
        SerializerInterface $serializer
    ) {
        $this->repository = $repository;
        $this->serializer = $serializer;
    }

    #[Route(path: '/api/search', name: 'api_event_search', methods: 'GET')]
    public function __invoke(Request $request): JsonResponse
    {
        /** @var SearchEventFilterPayload $searchInput */
        $searchInput = $this->serializer->denormalize($request->query->all(), SearchEventFilterPayload::class);
        $searchEventFilter = $searchInput->createSearchEventFilter();

        $countByType = $this->repository->countByType($searchEventFilter);

        $data = [
            'meta' => [
                'totalEvents' => $this->repository->countAll($searchEventFilter),
                'totalPullRequests' => $countByType[EventType::PULL_REQUEST] ?? 0,
                'totalCommits' => $countByType[EventType::COMMIT] ?? 0,
                'totalComments' => $countByType[EventType::COMMENT] ?? 0,
            ],
            'data' => [
                'events' => $this->repository->getLatest($searchEventFilter),
                'stats' => $this->repository->statsByTypePerHour($searchEventFilter),
            ],
        ];

        return new JsonResponse($data);
    }
}

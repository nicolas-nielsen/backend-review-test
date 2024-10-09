<?php

declare(strict_types=1);

namespace App\Application\Controller\Event\Read;

use App\Application\Dto\Event\SearchEventFilterPayload;
use App\Domain\Event\EventType;
use App\Domain\Event\Repository\ReadEventRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GetEventsForSearchAction
{
    #[Route(path: '/api/search', name: 'api_event_search', methods: 'GET')]
    public function __invoke(
        Request $request,
        ValidatorInterface $validator,
        ReadEventRepository $repository,
        SerializerInterface $serializer
    ): JsonResponse {
        /** @var SearchEventFilterPayload $searchInput */
        $searchInput = $serializer->denormalize($request->query->all(), SearchEventFilterPayload::class);

        $errors = $validator->validate($searchInput);

        if (\count($errors) > 0) {
            return new JsonResponse(
                ['message' => $errors->get(0)->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }

        $searchEventFilter = $searchInput->createSearchEventFilter();

        $countByType = $repository->countByType($searchEventFilter);

        $data = [
            'meta' => [
                'totalEvents' => $repository->countAll($searchEventFilter),
                'totalPullRequests' => $countByType[EventType::PULL_REQUEST] ?? 0,
                'totalCommits' => $countByType[EventType::COMMIT] ?? 0,
                'totalComments' => $countByType[EventType::COMMENT] ?? 0,
            ],
            'data' => [
                'events' => $repository->getLatest($searchEventFilter),
                'stats' => $repository->statsByTypePerHour($searchEventFilter),
            ],
        ];

        return new JsonResponse($data);
    }
}

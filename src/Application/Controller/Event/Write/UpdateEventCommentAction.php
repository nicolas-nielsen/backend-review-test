<?php

namespace App\Application\Controller\Event\Write;

use App\Application\Dto\Event\UpdateCommentPayload;
use App\Domain\Event\Repository\ReadEventRepository;
use App\Domain\Event\Repository\WriteEventRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateEventCommentAction
{
    public function __construct(
        private readonly WriteEventRepository $writeEventRepository,
        private readonly ReadEventRepository $readEventRepository,
        private readonly SerializerInterface $serializer
    ) {
    }

    #[Route(path: '/api/event/{id}/update', name: 'api_event_comment_update', methods: 'PUT')]
    public function __invoke(Request $request, int $id, ValidatorInterface $validator): Response
    {
        /** @var UpdateCommentPayload $eventInput */
        $eventInput = $this->serializer->deserialize($request->getContent(), UpdateCommentPayload::class, 'json');

        $errors = $validator->validate($eventInput);

        if (\count($errors) > 0) {
            return new JsonResponse(
                ['message' => $errors->get(0)->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }

        $updateCommentData = $eventInput->createUpdateCommentData();

        if($this->readEventRepository->exist($id) === false) {
            return new JsonResponse(
                ['message' => sprintf('Event identified by %d not found !', $id)],
                Response::HTTP_NOT_FOUND
            );
        }

        try {
            $this->writeEventRepository->update($updateCommentData, $id);
        } catch (\Exception $exception) {
            return new Response(null, Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}

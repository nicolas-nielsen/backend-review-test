<?php

declare(strict_types=1);

namespace App\Command;

use App\Dto\EventType;
use App\Entity\Actor;
use App\Entity\Event;
use App\Entity\Repo;
use App\Provider\GhArchiveProvider;
use App\Repository\ReadActorRepository;
use App\Repository\ReadEventRepository;
use App\Repository\ReadRepoRepository;
use App\Repository\WriteActorRepository;
use App\Repository\WriteEventRepository;
use App\Repository\WriteRepoRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * This command must import GitHub events.
 * You can add the parameters and code you want in this command to meet the need.
 */
class ImportGitHubEventsCommand extends Command
{
    private const BATCH_SIZE = 500;

    public function __construct(
        private readonly GhArchiveProvider $ghArchiveProvider,
        private readonly WriteEventRepository $eventRepository,
        private readonly WriteRepoRepository $repoRepository,
        private readonly WriteActorRepository $actorRepository,
        private readonly ReadActorRepository $readActorRepository,
        private readonly ReadRepoRepository $readRepoRepository,
        private readonly ReadEventRepository $readEventRepository
    ) {
        parent::__construct();
    }

    protected static $defaultName = 'app:import-github-events';

    protected function configure(): void
    {
        $this
            ->addArgument('date', InputArgument::REQUIRED, 'date and time to import events from, must follow YYYY-MM-DD-HH format')
            ->setDescription('Import GH events');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $date = \DateTime::createFromFormat('Y-m-d-H', $input->getArgument('date'));
        if (!$date) {
            $io->error('Invalid date, date must follow YYYY-MM-DD-HH format');

            return Command::FAILURE;
        }

        if ($date > new \DateTime()) {
            $io->error('Invalid date, date can\'t be in the future');

            return Command::FAILURE;
        }

        $date = $date->format('Y-m-d-H');


        $batchCount = 0;
        $events = [];
        $actors = [];
        $repos = [];

        $ghArchiveEvents = $this->ghArchiveProvider->fetchEvents($date);

        $io->progressStart();

        foreach ($ghArchiveEvents as $ghArchiveEvent) {
            $io->progressAdvance();

            $actorData = $ghArchiveEvent->actor;
            $actor = new Actor(
                $actorData->id,
                $actorData->login,
                $actorData->url,
                $actorData->avatar_url
            );

            if (!array_key_exists($actor->id(), $actors) && !$this->readActorRepository->exist($actor)) {
                $actors[$actor->id()] = $actor;
            }

            $repoData = $ghArchiveEvent->repo;
            $repo = new Repo(
                $repoData->id,
                $repoData->name,
                $repoData->url
            );
            if (!array_key_exists($repo->id(), $repos) && !$this->readRepoRepository->exist($repo)) {
                $repos[$repo->id()] = $repo;
            }

            $event = new Event(
                $ghArchiveEvent->id,
                EventType::createFromGhArchiveEvent($ghArchiveEvent->type)->value,
                $actor,
                $repo,
                $ghArchiveEvent->payload,
                new \DateTimeImmutable($ghArchiveEvent->created_at),
                null
            );

            if (!$this->readEventRepository->exist($event->id())) {
                $events[$event->id()] = $event;
                $batchCount++;
            }


            if ($batchCount === self::BATCH_SIZE) {
                $this->actorRepository->insertBatch($actors);
                $this->repoRepository->insertBatch($repos);
                $this->eventRepository->insertBatch($events);

                unset($actors, $repos, $events);
                $actors = [];
                $repos = [];
                $events = [];

                $batchCount = 0;
            }
        }

        $this->actorRepository->insertBatch($actors);
        $this->repoRepository->insertBatch($repos);
        $this->eventRepository->insertBatch($events);

        $io->progressFinish();
        $io->success('Import finished');

        return Command::SUCCESS;
    }
}

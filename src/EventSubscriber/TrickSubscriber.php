<?php

namespace App\EventSubscriber;

use App\Event\TrickCreatedEvent;
use App\Event\TrickUpdatedEvent;
use App\Service\TrickService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TrickSubscriber implements EventSubscriberInterface
{
    public function __construct(private TrickService $trickService)
    {
    }

    public function onTrickCreated(TrickCreatedEvent $event): void
    {
        $trick = $event->getTrick();

        $this->trickService->setSlug($trick);
        $this->trickService->setMainPicture($trick);
        $trick->setCreatedAt(new \DateTimeImmutable());
    }

    public function onTrickUpdated(TrickUpdatedEvent $event): void
    {
        $trick = $event->getTrick();

        $this->trickService->setSlug($trick);
        $this->trickService->setMainPicture($trick);
        $trick->setUpdatedAt(new \DateTimeImmutable());
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TrickCreatedEvent::class => 'onTrickCreated',
            TrickUpdatedEvent::class => 'onTrickUpdated',
        ];
    }
}

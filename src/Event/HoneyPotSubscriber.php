<?php

namespace App\Event;

use App\Event\HoneyPotEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class HoneyPotSubscriber implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $honeypotLogger, private RequestStack $requestStack)
    {
    }

    public function onHoneyPotEvent(HoneyPotEvent $event): void
    {
        $request = $this->requestStack->getMainRequest();
        $ip = $request->getClientIp();

        $this->honeypotLogger->alert("BAN HoneyPot - " . $ip);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            HoneyPotEvent::class => 'onHoneyPotEvent',
        ];
    }
}
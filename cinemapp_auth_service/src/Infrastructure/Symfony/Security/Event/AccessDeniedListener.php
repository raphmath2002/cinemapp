<?php

namespace Infrastructure\Symfony\Security\Event;

use Domain\Response\Auth\LoginResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class AccessDeniedListener implements EventSubscriberInterface
{

    public function __construct(
        protected SerializerInterface $serializer
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 2]
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if(!$exception instanceof AccessDeniedException) return;

        $response = new LoginResponse();

        $response->userNotGranted();

        $serializedResponse = $this->serializer->serialize($response, 'json', [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);

        $event->setResponse(new Response($serializedResponse, $response->statusCode, ['Content-Type' => "text/json"]));
    }
}
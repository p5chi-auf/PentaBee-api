<?php

namespace App\EventListener;

use App\Exception\ApiProblem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class PublishedMessageExceptionListener implements EventSubscriberInterface
{
    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        $exception = $event->getException();

        if ($exception instanceof ApiProblem) {
            $apiProblem = $exception->getStatusCode();
        } else {
            $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;
            $apiProblem = new ApiProblem(
                $statusCode
            );
        }
        $response = new JsonResponse(
            $apiProblem->toArray(),
            $apiProblem->getStatusCode()
        );
        $response->headers->set('Content-Type', 'application/problem+json');
        $event->setResponse($response);
    }

    public static function getSubscribedEvents(): array
    {
        return array(
            KernelEvents::EXCEPTION => 'onKernelException'
        );
    }
}

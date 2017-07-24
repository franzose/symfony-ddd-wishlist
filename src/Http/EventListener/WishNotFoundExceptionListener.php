<?php

namespace Wishlist\Http\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Wishlist\Domain\Exception\WishNotFoundException;

class WishNotFoundExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if (!$this->isSatisfiedByEvent($event)) {
            return;
        }

        $response = new JsonResponse(['error' => $exception->getMessage()], 404);

        $event->setResponse($response);
    }

    private function isSatisfiedByEvent(GetResponseForExceptionEvent $event)
    {
        return $event->getException() instanceof WishNotFoundException &&
               $event->getRequest()->isXmlHttpRequest();
    }
}

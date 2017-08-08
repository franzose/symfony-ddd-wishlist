<?php

namespace Wishlist\Http\EventListener;

use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Wishlist\Domain\Exception\DomainExceptionInterface;
use Wishlist\Domain\Exception\InvalidOperationExceptionInterface;
use Wishlist\Domain\Exception\NotFoundExceptionInterface;

class DomainExceptionListener
{
    private static $exceptionClassesToHttpCodes = [
        NotFoundExceptionInterface::class => Response::HTTP_NOT_FOUND,
        InvalidOperationExceptionInterface::class => Response::HTTP_UNPROCESSABLE_ENTITY
    ];

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if (!$this->isSatisfiedByEvent($event)) {
            return;
        }

        $response = new JsonResponse(
            [
                'success' => false,
                'message' => $exception->getMessage()
            ],
            $this->getResponseCodeFromException($exception)
        );

        $event->setResponse($response);
    }

    private function isSatisfiedByEvent(GetResponseForExceptionEvent $event)
    {
        return $event->getException() instanceof DomainExceptionInterface &&
               $event->getRequest()->isXmlHttpRequest();
    }

    private function getResponseCodeFromException(Exception $exception)
    {
        foreach (static::$exceptionClassesToHttpCodes as $class => $code) {
            if ($exception instanceof $class) {
                return $code;
            }
        }

        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}

<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if($exception instanceof NotFoundHttpException) {
            $data = [
                'status' => $exception->getStatusCode(),
                'message' => 'Resource not found'
            ];

            $response = new JsonResponse($data);
            $event->setResponse($response);
        } elseif ($exception instanceof AccessDeniedHttpException) {
            $data = [
                'status' => $exception->getStatusCode(),
                'message' => 'Access denied'
            ];

            $response = new JsonResponse($data);
            $event->setResponse($response);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
           'kernel.exception' => 'onKernelException',
        ];
    }
}
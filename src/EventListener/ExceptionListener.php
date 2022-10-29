<?php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $title = "Request Error";

        //Todo make response specific to exception
        $response = new JsonResponse([
            "title" => $title,
            "description" => $exception->getMessage(),
        ]);

        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
        } else {
            $logErrorId = uniqid();

            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            $response->setData([
                "title" => "SERVER ERROR",
                "description" => "The server encountered an error while processing your response",
                "message" => "Please contact administrator with your log error Id to help solve it",
                "LOG_ERROR_ID" => $logErrorId
            ]);

            /** @var string $logData */
            $logData = json_encode([
                "LOG_ERROR_ID" => $logErrorId,
                "exception class" => $exception::class,
                "exception message" => $exception->getMessage(),
                "exception trace" => $exception->getTraceAsString()
            ]);

            $this->logger->error($logData);
        }

        $event->setResponse($response);
    }
}

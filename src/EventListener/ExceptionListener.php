<?php

namespace App\EventListener;

use ApiPlatform\Exception\ItemNotFoundException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Throwable;

class ExceptionListener
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $logErrorId = uniqid();


        list($response, $isSymfonyHttpException) = $this->createResponseAccordingToExceptionType($exception, $logErrorId);

        $this->logNonSymfonyHttpException($logErrorId, $exception, $isSymfonyHttpException);

        $event->setResponse($response);
    }

    /**
     * @param string $logErrorId
     * @param Throwable $exception
     * @param bool $isSymfonyHttpException
     * @return void
     */
    public function logNonSymfonyHttpException(string $logErrorId, mixed $exception, bool $isSymfonyHttpException): void
    {
        /** @var string $logData */
        $logData = json_encode([
            "LOG_ERROR_ID" => $logErrorId,
            "exception class" => $exception::class,
            "exception message" => $exception->getMessage(),
            "exception trace" => $exception->getTraceAsString()
        ]);

        if (!$isSymfonyHttpException) {
            $this->logger->error($logData);
        }
    }

    /**
     * @param Throwable $exception
     * @param string $logErrorId
     * @return array{JsonResponse, bool}
     */
    public function createResponseAccordingToExceptionType(Throwable $exception, string $logErrorId): array
    {
        $response = new JsonResponse([
            "title" => "Request error",
            "description" => $exception->getMessage(),
            "LOG_ERROR_ID" => $logErrorId
        ]);
        $isSymfonyHttpException = false;

        if ($exception instanceof HttpExceptionInterface) {
            $isSymfonyHttpException = true;
            $response->setStatusCode($exception->getStatusCode());
        } elseif ($exception instanceof UnexpectedValueException) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $responseData = [
                "title" => "Unexpected value error.",
                "description" => $exception->getMessage(),
            ];

            if ($exception->getPrevious() instanceof NotNormalizableValueException
                || $exception->getPrevious() instanceof ItemNotFoundException
            ) {
                $responseData["message"] = $exception->getPrevious()->getMessage();
            }
            $response->setData($responseData);
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            $response->setData([
                "title" => "SERVER ERROR",
                "description" => "The server encountered an error while processing your response",
                "message" => "Please contact administrator with your log error Id to help solve it",
            ]);
        }
        return array($response, $isSymfonyHttpException);
    }
}

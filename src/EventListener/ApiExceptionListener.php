<?php

namespace App\EventListener;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener(event: KernelEvents::EXCEPTION)]
final class ApiExceptionListener
{
    public function __construct(
        #[Autowire(service: 'serializer.name_converter.camel_case_to_snake_case')]
        private readonly NameConverterInterface $nameConverter,
        #[Autowire('%kernel.debug%')]
        private readonly bool $debug,
    ) {}

    public function __invoke(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        if (! str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $exception = $event->getThrowable();

        if ($exception->getPrevious() instanceof ValidationFailedException) {
            $errors = [];
            foreach ($exception->getPrevious()->getViolations() as $violation) {
                $field = $this->nameConverter->normalize($violation->getPropertyPath()) ?: 'body';
                $errors[$field][] = $violation->getMessage();
            }

            $event->setResponse($this->json($errors, JsonResponse::HTTP_UNPROCESSABLE_ENTITY));

            return;
        }

        // 404: entity not found or unknown /api route
        if ($exception instanceof NotFoundHttpException) {
            $field = $request->attributes->has('id') ? 'id' : 'route';
            $event->setResponse($this->json([$field => ['Resource not found.']], JsonResponse::HTTP_NOT_FOUND));

            return;
        }

        if ($exception instanceof HttpExceptionInterface) {
            $event->setResponse($this->json(
                ['request' => [$exception->getMessage()]],
                $exception->getStatusCode(),
                $exception->getHeaders(),
            ));

            return;
        }

        if ($this->debug) {
            return;
        }

        $event->setResponse($this->json(
            ['server' => ['Internal server error']],
            JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
        ));
    }


    private function json(array $errors, int $status, array $headers = []): JsonResponse
    {
        $response = new JsonResponse(['errors' => $errors], $status, $headers);
        $response->setEncodingOptions(JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return $response;
    }
}
